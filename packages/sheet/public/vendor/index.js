//#region src/index.ts
const sheets = /* @__PURE__ */ new WeakMap();
/** Open sheets, the most recently opened last. */
const stack = [];
const hasScrollEnd = typeof window != "undefined" && "onscrollend" in window;
const reducedMotion = () => matchMedia("(prefers-reduced-motion: reduce)").matches;
const emit = (dialog, type, detail, cancelable = false) => dialog.dispatchEvent(new CustomEvent(`ss:${type}`, {
	detail,
	cancelable
}));
const unstack = (sheet) => {
	const index = stack.indexOf(sheet);
	if (index >= 0) stack.splice(index, 1);
	sheet.dialog.removeAttribute("data-ss-covered");
	stack[stack.length - 1]?.dialog.removeAttribute("data-ss-covered");
	if (!stack.length) document.documentElement.style.removeProperty("--ss-gutter");
};
function attach(dialog, options = {}) {
	const existing = sheets.get(dialog);
	if (existing) return existing;
	install();
	const panel = dialog.querySelector(":scope > .ss-panel");
	const rest = dialog.querySelector(":scope > .ss-rest");
	let invoker = null;
	let before = null;
	let reason = "native";
	let snap = -1;
	let placedOn = null;
	let pressedOnDialog = false;
	let opening = false;
	let scrollTimer = 0;
	let frame = 0;
	/** The close in progress: its promise, how to stop its animation, how to settle it. */
	let pending = null;
	const cleanups = [];
	const listen = (target, type, handler, capture = false) => {
		target.addEventListener(type, handler, capture);
		cleanups.push(() => target.removeEventListener(type, handler, capture));
	};
	const axis = () => {
		const style = getComputedStyle(dialog);
		return style.overflowX != "hidden" ? "x" : style.overflowY != "hidden" ? "y" : null;
	};
	const rtl = () => getComputedStyle(dialog).direction == "rtl";
	const restFirst = () => !!rest && (axis() == "x" ? rest.offsetLeft < panel.offsetLeft !== rtl() : rest.offsetTop < panel.offsetTop);
	const travel = () => {
		const x = axis() == "x";
		const max = x ? dialog.scrollWidth - dialog.clientWidth : dialog.scrollHeight - dialog.clientHeight;
		const position = Math.abs(x ? dialog.scrollLeft : dialog.scrollTop);
		return {
			at: restFirst() ? max - position : position,
			max
		};
	};
	const markers = () => [...panel.querySelectorAll(".ss-snap")].filter((marker) => marker.offsetParent && marker.closest("dialog") == dialog);
	const shownAt = (marker) => Math.min(marker.offsetTop + marker.offsetHeight, panel.offsetHeight);
	const snapPoints = () => {
		if (axis() != "y") return axis() ? [panel.offsetWidth] : [];
		return [.../* @__PURE__ */ new Set([...markers().map(shownAt), panel.offsetHeight])].sort((a, b) => a - b);
	};
	const nearest = (points, shown) => points.reduce((best, point, index) => Math.abs(point - shown) < Math.abs(points[best] - shown) ? index : best, 0);
	const current = () => {
		const points = snapPoints();
		if (!dialog.open || !points.length) return -1;
		const shown = (axis() == "x" ? panel.offsetWidth : panel.offsetHeight) - travel().at;
		return shown < points[0] / 2 ? -1 : nearest(points, shown);
	};
	const scrollToTravel = (distance, instant) => {
		const behavior = instant || reducedMotion() ? "instant" : "smooth";
		const { max } = travel();
		const position = restFirst() ? max - distance : distance;
		if (axis() == "x") dialog.scrollTo({
			left: rtl() ? -position : position,
			behavior
		});
		else dialog.scrollTo({
			top: position,
			behavior
		});
	};
	const snapTo = (target, { instant } = {}) => {
		const points = snapPoints();
		placedOn = axis();
		if (!points.length) return;
		const index = target == "full" ? points.length - 1 : Math.max(0, Math.min(points.length - 1, target));
		scrollToTravel(points[points.length - 1] - points[index], instant);
	};
	const updateSnap = () => {
		const points = snapPoints();
		const next = current();
		dialog.toggleAttribute("data-ss-expanded", dialog.open && (axis() != "y" || next == points.length - 1));
		if (next == snap) return;
		snap = next;
		emit(dialog, "snap", {
			snap,
			snapPoints: points
		});
	};
	const onSettled = () => {
		if (!dialog.open || pending) return;
		const { at, max } = travel();
		const away = max > 0 && at >= max - 1;
		if (opening) {
			if (away) return;
			opening = false;
		}
		if (away) {
			sheet.requestClose("swipe").then((closed) => closed || snapTo(Math.max(snap, 0)));
			return;
		}
		updateSnap();
	};
	/** Ends the close in progress: false when it was called off, true after the dialog closed. */
	const settle = (closed) => {
		const current = pending;
		if (!current) return;
		pending = null;
		current.stop();
		delete dialog.dataset.ssClosing;
		current.settle(closed);
	};
	const finish = () => {
		if (dialog.open || !stack.includes(sheet)) return;
		delete dialog.dataset.ssClosing;
		dialog.removeAttribute("data-ss-expanded");
		unstack(sheet);
		opening = false;
		snap = -1;
		const top = stack[stack.length - 1];
		const now = document.activeElement;
		const free = !now || now == document.body || now == before || dialog.contains(now);
		if (invoker?.isConnected && free && (!top || top.dialog.contains(invoker))) invoker.focus({ preventScroll: true });
		invoker = before = null;
		const why = reason;
		reason = "native";
		emit(dialog, "close", { reason: why });
		settle(true);
	};
	const sheet = {
		dialog,
		get state() {
			return {
				open: dialog.open,
				snapPoints: snapPoints(),
				snap: current()
			};
		},
		open({ invoker: from, snap: initial } = {}) {
			if (dialog.open) {
				if (pending) {
					settle(false);
					snapTo(snap >= 0 ? snap : "full");
				}
				return;
			}
			finish();
			if (options.replace || dialog.hasAttribute("data-ss-replace")) {
				for (const other of [...stack]) if (other != sheet) other.close("replaced");
			}
			const active = before = document.activeElement;
			invoker = from ?? (active != document.body ? active : null);
			const page = document.documentElement;
			const short = page.scrollHeight <= page.clientHeight;
			if (dialog.hasAttribute("data-ss-depth")) {
				const behind = document.querySelector("[data-ss-page]") ?? document.body;
				behind.style.setProperty("--ss-page-y", `${-behind.getBoundingClientRect().top}px`);
			}
			if (dialog.dataset.ssModal == "false") dialog.show();
			else dialog.showModal();
			if (viaPointer && !document.activeElement?.hasAttribute("autofocus")) {
				if (!dialog.hasAttribute("tabindex")) dialog.tabIndex = -1;
				dialog.focus({ preventScroll: true });
			}
			stack[stack.length - 1]?.dialog.setAttribute("data-ss-covered", "");
			stack.push(sheet);
			if (stack.length == 1 && short) page.style.setProperty("--ss-gutter", "auto");
			const marked = markers().find((marker) => marker.hasAttribute("data-ss-initial"));
			const target = initial ?? (marked ? snapPoints().indexOf(shownAt(marked)) : "full");
			opening = !!axis() && !reducedMotion();
			if (opening) scrollToTravel(travel().max, true);
			snapTo(target);
			if (!opening) updateSnap();
			emit(dialog, "open", {
				invoker,
				snap: target
			});
		},
		requestClose(why = "api") {
			if (!dialog.open) return Promise.resolve(false);
			if (pending) return pending.promise;
			if (dialog.dataset.ssDismissible == "false" && why != "button" && why != "api" || !emit(dialog, "requestclose", { reason: why }, true)) return Promise.resolve(false);
			reason = why;
			let resolve;
			const promise = new Promise((done) => resolve = done);
			let timer = 0;
			let stopListening = () => {};
			pending = {
				promise,
				stop: () => {
					clearTimeout(timer);
					stopListening();
				},
				settle: resolve
			};
			const leave = () => {
				pending?.stop();
				if (axis()) scrollToTravel(0, true);
				dialog.close();
			};
			const { at, max } = travel();
			if (max > 0 && at >= max - 1 || reducedMotion()) {
				leave();
				return promise;
			}
			dialog.dataset.ssClosing = "";
			const scrolls = !!axis() && max > 0;
			if (scrolls) scrollToTravel(max);
			const target = scrolls ? dialog : panel;
			const type = scrolls ? hasScrollEnd ? "scrollend" : "scroll" : "transitionend";
			const onEnd = (event) => {
				if (event.type == "transitionend" ? event.target == panel : travel().at >= travel().max - 1) leave();
			};
			target.addEventListener(type, onEnd);
			stopListening = () => target.removeEventListener(type, onEnd);
			timer = setTimeout(leave, scrolls ? 700 : parseFloat(getComputedStyle(panel).transitionDuration) * 1e3 + 80);
			return promise;
		},
		close(why = "api") {
			if (!dialog.open) return;
			pending?.stop();
			reason = why;
			if (axis()) scrollToTravel(0, true);
			dialog.close();
		},
		snapTo,
		destroy() {
			if (!sheets.has(dialog)) return;
			cleanups.forEach((cleanup) => cleanup());
			clearTimeout(scrollTimer);
			cancelAnimationFrame(frame);
			settle(false);
			unstack(sheet);
			dialog.style.removeProperty("scroll-snap-type");
			dialog.removeAttribute("data-ss-expanded");
			delete dialog.dataset.ssReady;
			sheets.delete(dialog);
		}
	};
	listen(dialog, "command", (event) => {
		if (event.command == "show-modal") {
			event.preventDefault();
			sheet.open({ invoker: event.source });
		} else if (event.command == "close" || event.command == "request-close") {
			event.preventDefault();
			sheet.requestClose("button");
		} else if (event.command == "--ss-cycle") cycle(sheet);
	});
	listen(dialog, "keydown", (event) => {
		const own = event.target.closest("dialog") == dialog;
		if (event.key == "Escape" && own && !dialog.matches(":modal")) sheet.requestClose("escape");
	});
	listen(dialog, "cancel", (event) => {
		event.preventDefault();
		sheet.requestClose("escape");
	});
	listen(dialog, "pointerdown", (event) => pressedOnDialog = event.target == dialog);
	listen(dialog, "click", (event) => {
		if (event.target == dialog && pressedOnDialog) sheet.requestClose("backdrop");
		pressedOnDialog = false;
	});
	listen(dialog, hasScrollEnd ? "scrollend" : "scroll", () => {
		if (hasScrollEnd) return onSettled();
		clearTimeout(scrollTimer);
		scrollTimer = setTimeout(onSettled, 120);
	});
	listen(dialog, "close", finish);
	listen(window, "resize", () => {
		if (!dialog.open || pending) return;
		if (axis() != placedOn) snapTo("full", { instant: true });
		updateSnap();
	});
	const adopting = dialog.open;
	let shownBefore = 0;
	if (adopting) {
		panel.getAnimations?.().forEach((animation) => animation.finish());
		shownBefore = dialog.getBoundingClientRect().bottom - panel.getBoundingClientRect().top;
		dialog.style.scrollSnapType = "none";
	}
	dialog.dataset.ssReady = "";
	sheets.set(dialog, sheet);
	for (const plugin of options.plugins ?? []) {
		const cleanup = plugin(sheet);
		if (cleanup) cleanups.push(cleanup);
	}
	if (adopting) {
		stack.push(sheet);
		const points = snapPoints();
		const target = axis() == "y" ? nearest(points, shownBefore) : "full";
		snapTo(target, { instant: true });
		frame = requestAnimationFrame(() => dialog.style.removeProperty("scroll-snap-type"));
		updateSnap();
		emit(dialog, "open", {
			invoker: null,
			snap: target
		});
	}
	return sheet;
}
/** Up to the next snap point, from the full height back to the lowest; like the handle of a native sheet. */
const cycle = (sheet) => {
	const { snap, snapPoints } = sheet.state;
	if (snapPoints.length > 1) sheet.snapTo((snap + 1) % snapPoints.length);
};
/** The sheet attached to a dialog, if any. */
const getSheet = (dialog) => sheets.get(dialog);
/** The most recently opened sheet that is still open: where toasts and popovers belong. */
const topSheet = () => stack[stack.length - 1];
/**
* Asks the top sheet to close, e.g. from a native app's back button. Returns whether a sheet was
* open to ask.
*/
function closeTop(reason = "api") {
	const top = topSheet();
	if (top) top.requestClose(reason);
	return !!top;
}
/** Whether the last input was a pointer (tap, click) rather than a key. */
let viaPointer = false;
let installed = false;
/**
* Once per page: notes whether the last input was a pointer or a key, and in browsers without
* invoker commands lets commandfor buttons open and close attached sheets.
*/
function install() {
	if (installed || typeof document == "undefined") return;
	installed = true;
	document.addEventListener("pointerdown", () => viaPointer = true, true);
	document.addEventListener("keydown", () => viaPointer = false, true);
	if ("commandForElement" in HTMLButtonElement.prototype) return;
	document.addEventListener("click", (event) => {
		const button = event.target.closest?.("button[commandfor]");
		const dialog = button && document.getElementById(button.getAttribute("commandfor"));
		const sheet = dialog instanceof HTMLDialogElement && sheets.get(dialog);
		const command = button?.getAttribute("command");
		if (!sheet) return;
		if (command == "show-modal") sheet.open({ invoker: button });
		else if (command == "close" || command == "request-close") sheet.requestClose("button");
		else if (command == "--ss-cycle") cycle(sheet);
	});
}
/** Attaches to every dialog.ss under root. */
function enhance(root = document, options = {}) {
	return [...root.querySelectorAll("dialog.ss")].map((dialog) => attach(dialog, options));
}
//#endregion
export { attach, closeTop, enhance, getSheet, topSheet };
