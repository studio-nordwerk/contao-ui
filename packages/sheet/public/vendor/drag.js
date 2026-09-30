//#region src/drag.ts
function drag({ area = ".ss-handle, .ss-header", threshold = 4 } = {}) {
	return (sheet) => {
		const { dialog } = sheet;
		const panel = dialog.querySelector(":scope > .ss-panel");
		let press = null;
		let suppressUntil = 0;
		let timer = 0;
		const cleanups = [];
		const listen = (target, type, handler, capture = false) => {
			target.addEventListener(type, handler, capture);
			cleanups.push(() => target.removeEventListener(type, handler, capture));
		};
		const axis = () => {
			const style = getComputedStyle(dialog);
			return style.overflowX != "hidden" ? "x" : style.overflowY != "hidden" ? "y" : null;
		};
		const shown = (x) => {
			const p = panel.getBoundingClientRect();
			const d = dialog.getBoundingClientRect();
			return x ? Math.min(p.right, d.right) - Math.max(p.left, d.left) : Math.min(p.bottom, d.bottom) - Math.max(p.top, d.top);
		};
		const restore = () => {
			clearTimeout(timer);
			dialog.style.removeProperty("scroll-snap-type");
		};
		dialog.setAttribute("data-ss-drag", "");
		listen(panel, "dragstart", (event) => press && event.preventDefault());
		listen(dialog, "click", (event) => {
			if (event.timeStamp > suppressUntil) return;
			event.preventDefault();
			event.stopPropagation();
		}, true);
		listen(panel, "pointerdown", (event) => {
			const direction = axis();
			const target = event.target;
			if (event.pointerType != "mouse" || event.button || !direction) return;
			if (target.closest("dialog") != dialog || !target.closest(area)) return;
			if (target.closest("input, textarea, select, [contenteditable]")) return;
			press = {
				id: event.pointerId,
				x: direction == "x",
				from: [event.clientX, event.clientY],
				scroll: [dialog.scrollLeft, dialog.scrollTop],
				moved: false,
				trail: []
			};
		});
		listen(document, "pointermove", (event) => {
			if (!press || event.pointerId != press.id) return;
			if (!(event.buttons & 1)) return release(event);
			const delta = press.x ? event.clientX - press.from[0] : event.clientY - press.from[1];
			if (!press.moved) {
				if (Math.abs(delta) < threshold) return;
				press.moved = true;
				clearTimeout(timer);
				dialog.style.scrollSnapType = "none";
				dialog.setAttribute("data-ss-dragging", "");
				panel.setPointerCapture(press.id);
				getSelection()?.removeAllRanges();
			}
			dialog.scrollTo(press.x ? {
				left: press.scroll[0] - delta,
				behavior: "instant"
			} : {
				top: press.scroll[1] - delta,
				behavior: "instant"
			});
			press.trail.push([event.timeStamp, shown(press.x)]);
			while (press.trail.length > 2 && event.timeStamp - press.trail[0][0] > 100) press.trail.shift();
		});
		const release = (event) => {
			const done = press;
			if (!done || event.pointerId != done.id) return;
			press = null;
			if (!done.moved) return;
			dialog.removeAttribute("data-ss-dragging");
			suppressUntil = event.timeStamp + 100;
			if (!dialog.open) return restore();
			requestAnimationFrame(() => dialog.addEventListener("scrollend", restore, { once: true }));
			timer = setTimeout(restore, 800);
			const points = sheet.state.snapPoints;
			const now = shown(done.x);
			const [t0, s0] = done.trail.find(([t]) => event.timeStamp - t <= 100) ?? [event.timeStamp, now];
			const aim = now + (event.timeStamp > t0 ? (now - s0) / (event.timeStamp - t0) * 200 : 0);
			if (aim < points[0] / 2) {
				sheet.requestClose("swipe").then((closed) => closed || sheet.snapTo(0));
				return;
			}
			sheet.snapTo(points.reduce((best, point, i) => Math.abs(point - aim) < Math.abs(points[best] - aim) ? i : best, 0));
		};
		listen(document, "pointerup", release);
		listen(document, "pointercancel", release);
		return () => {
			cleanups.forEach((cleanup) => cleanup());
			restore();
			dialog.removeAttribute("data-ss-drag");
			dialog.removeAttribute("data-ss-dragging");
		};
	};
}
//#endregion
export { drag };
