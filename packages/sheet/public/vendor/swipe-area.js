//#region src/swipe-area.ts
function swipeArea({ element, size = "1.5rem", threshold = 8 } = {}) {
	return (sheet) => {
		const { dialog } = sheet;
		const panel = dialog.querySelector(":scope > .ss-panel");
		const area = element ?? document.body.appendChild(document.createElement("div"));
		let edge = null;
		let press = null;
		let timer = 0;
		const cleanups = [];
		const listen = (target, type, handler) => {
			target.addEventListener(type, handler);
			cleanups.push(() => target.removeEventListener(type, handler));
		};
		const place = () => {
			const style = getComputedStyle(dialog);
			const x = style.overflowX != "hidden";
			const first = style.getPropertyValue("--_ready-rest-order").trim() == "-1";
			edge = x ? first != (style.direction == "rtl") ? "right" : "left" : style.overflowY != "hidden" ? first ? "bottom" : "top" : null;
			area.dataset.ssEdge = edge ?? "none";
			if (element) return;
			const across = edge == "top" || edge == "bottom" ? ["left", "right"] : ["top", "bottom"];
			for (const side of [
				"top",
				"right",
				"bottom",
				"left"
			]) area.style.setProperty(side, side == edge || across.includes(side) ? "0" : "auto");
			area.style.cssText += `;position:fixed;z-index:1;touch-action:none;display:${edge ? "block" : "none"}`;
			const flat = across[0] == "left";
			area.style.setProperty("height", flat ? size : "auto");
			area.style.setProperty("width", flat ? "auto" : size);
		};
		const horizontal = () => edge == "left" || edge == "right";
		const shown = () => {
			const p = panel.getBoundingClientRect();
			const d = dialog.getBoundingClientRect();
			return horizontal() ? Math.min(p.right, d.right) - Math.max(p.left, d.left) : Math.min(p.bottom, d.bottom) - Math.max(p.top, d.top);
		};
		const restore = () => {
			clearTimeout(timer);
			dialog.style.removeProperty("scroll-snap-type");
		};
		if (!element) area.setAttribute("aria-hidden", "true");
		place();
		listen(window, "resize", place);
		listen(area, "pointerdown", (event) => {
			if (dialog.open || !edge || event.button) return;
			press = {
				id: event.pointerId,
				from: [event.clientX, event.clientY],
				scroll: [0, 0],
				open: false,
				trail: []
			};
		});
		listen(document, "pointermove", (event) => {
			if (!press || event.pointerId != press.id) return;
			const x = horizontal();
			const along = x ? event.clientX - press.from[0] : event.clientY - press.from[1];
			if (!press.open) {
				const across = Math.abs(x ? event.clientY - press.from[1] : event.clientX - press.from[0]);
				const away = edge == "bottom" || edge == "right" ? -along : along;
				if (across > threshold && across > away) press = null;
				if (!press || away < threshold) return;
				press.open = true;
				dialog.style.scrollSnapType = "none";
				sheet.open();
				if (!dialog.hasAttribute("open") || matchMedia("(prefers-reduced-motion: reduce)").matches) {
					press = null;
					return restore();
				}
				press.from = [event.clientX, event.clientY];
				press.scroll = [dialog.scrollLeft, dialog.scrollTop];
				dialog.scrollTo({
					left: press.scroll[0],
					top: press.scroll[1],
					behavior: "instant"
				});
				return;
			}
			dialog.scrollTo(x ? {
				left: press.scroll[0] - along,
				behavior: "instant"
			} : {
				top: press.scroll[1] - along,
				behavior: "instant"
			});
			press.trail.push([event.timeStamp, shown()]);
			while (press.trail.length > 2 && event.timeStamp - press.trail[0][0] > 100) press.trail.shift();
		});
		const release = (event) => {
			const done = press;
			if (!done || event.pointerId != done.id) return;
			press = null;
			if (!done.open || !dialog.open) return restore();
			requestAnimationFrame(() => dialog.addEventListener("scrollend", restore, { once: true }));
			timer = setTimeout(restore, 800);
			const points = sheet.state.snapPoints;
			const now = shown();
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
			if (!element) area.remove();
			else delete area.dataset.ssEdge;
		};
	};
}
//#endregion
export { swipeArea };
