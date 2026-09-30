import { a as snapPositions, i as pageOf, n as clamp, o as visibleRange, r as closest, t as buildPages } from "./shared/math-DhvRXjtu.js";
//#region src/index.ts
const LABELS = {
	page: (n, count) => `Page ${n} of ${count}`,
	status: (first, last, count) => first === last ? `Item ${first} of ${count}` : `Items ${first} to ${last} of ${count}`
};
const px = (value) => parseFloat(value) || 0;
/** A computed length-percentage in px; percentages of scroll-padding refer to the scrollport. */
const lengthIn = (value, whole) => value.trim().endsWith("%") ? px(value) * whole / 100 : px(value);
const instances = /* @__PURE__ */ new WeakMap();
/** The carousel attached to `root`, if any. */
const getCarousel = (root) => instances.get(root);
/** Attach to server-rendered markup. Attaching twice returns the same carousel. */
function attach(root, options = {}) {
	const existing = instances.get(root);
	if (existing) return existing;
	const track = root.querySelector("[data-sc-track]");
	const part = (name) => options[name] || root.querySelector(`[data-sc-${name}]`);
	const prevButton = part("prev");
	const nextButton = part("next");
	const dots = part("dots");
	const status = part("status");
	const labels = {
		...LABELS,
		...options.labels
	};
	const rewind = options.rewind === true ? "fade" : options.rewind || false;
	const motion = matchMedia("(prefers-reduced-motion: reduce)");
	const tabIndex = track.getAttribute("tabindex");
	const listeners = new AbortController();
	const listen = (target, type, handler, opts) => target?.addEventListener(type, handler, {
		signal: listeners.signal,
		...opts
	});
	const hooks = {
		control: [],
		settle: [],
		measure: []
	};
	const run = (name) => hooks[name].forEach((handler) => handler());
	let slides = [...track.children];
	const marked = slides.findIndex((slide) => slide.hasAttribute("data-sc-initial"));
	let anchor = Math.max(0, options.initial ?? marked);
	const anchorSlide = slides[anchor];
	let needsInitial = anchor > 0;
	let geo = {
		starts: [],
		sizes: [],
		port: 0,
		padStart: 0,
		padEnd: 0,
		max: 0,
		align: "start"
	};
	let snaps = [];
	let pages = [];
	let snap = "mandatory";
	let rtl = false;
	let pos = 0;
	let target = -1;
	let scrolling = false;
	let touching = false;
	let busy = false;
	let quiet = false;
	let state;
	let settled;
	let frame = 0;
	let measureFrame = 0;
	let settleTimer = 0;
	let fadeTimer = 0;
	const readPos = () => Math.abs(track.scrollLeft);
	const positions = () => pages.map((page) => page.pos);
	const moveTo = (position, behavior) => track.scrollTo({
		left: rtl ? -position : position,
		behavior
	});
	function measure() {
		slides = [...track.children];
		const style = getComputedStyle(track);
		rtl = style.direction == "rtl";
		const configured = String(options.group ?? style.getPropertyValue("--sc-group")).trim();
		const group = configured == "page" ? "page" : Math.max(1, parseInt(configured) || 1);
		if (!track.hasAttribute("data-sc-dragging")) {
			const type = style.scrollSnapType;
			snap = type == "none" ? "none" : type.includes("mandatory") ? "mandatory" : "proximity";
		}
		const box = track.getBoundingClientRect();
		const edge = rtl ? box.right - px(style.borderRightWidth) : box.left + px(style.borderLeftWidth);
		const offset = readPos();
		const port = track.clientWidth;
		geo = {
			starts: [],
			sizes: [],
			port,
			padStart: lengthIn(style.scrollPaddingInlineStart, port),
			padEnd: lengthIn(style.scrollPaddingInlineEnd, port),
			max: Math.max(0, track.scrollWidth - port),
			align: style.getPropertyValue("--sc-align").trim() || "start"
		};
		for (const slide of slides) {
			const rect = slide.getBoundingClientRect();
			geo.starts.push((rtl ? edge - rect.right : rect.left - edge) + offset);
			geo.sizes.push(rect.width);
		}
		snaps = snapPositions(geo);
		if (anchorSlide?.parentNode == track) anchor = slides.indexOf(anchorSlide);
		anchor = Math.max(0, Math.min(anchor, slides.length - 1));
		pages = buildPages(geo, snaps, group, anchor);
		if (target >= pages.length) target = -1;
		const firsts = new Set(pages.map((page) => page.first));
		slides.forEach((slide, i) => slide.toggleAttribute("data-sc-snap-off", group != 1 && !firsts.has(i)));
		if (dots) {
			while (dots.children.length > pages.length) dots.lastElementChild.remove();
			while (dots.children.length < pages.length) {
				const dot = document.createElement("button");
				dot.type = "button";
				dot.className = "sc-dot";
				dots.append(dot);
			}
			[...dots.children].forEach((dot, i) => dot.setAttribute("aria-label", labels.page(i + 1, pages.length)));
		}
		if (needsInitial && port && pages.length) {
			needsInitial = false;
			const initial = pages[pageOf(pages, anchor)].pos;
			if (Math.abs(readPos() - initial) > 1) moveTo(initial, "instant");
		}
		pos = readPos();
		run("measure");
	}
	function update(isSettled, silent) {
		const moving = target >= 0;
		const here = moving ? pages[target].pos : pos;
		const page = moving ? target : closest(positions(), pos);
		state = {
			index: moving ? pages[target].first : closest(snaps, pos),
			page,
			pageCount: pages.length,
			isBeginning: here <= 2,
			isEnd: here >= geo.max - 2,
			overflow: geo.max > 2
		};
		for (const [name, on] of [
			["overflow", state.overflow],
			["start", state.isBeginning],
			["end", state.isEnd]
		]) root.toggleAttribute("data-sc-" + name, on);
		if (tabIndex == "0") track.tabIndex = state.overflow ? 0 : -1;
		prevButton?.setAttribute("aria-disabled", String(state.isBeginning && !rewind));
		nextButton?.setAttribute("aria-disabled", String(state.isEnd && !rewind));
		if (dots) [...dots.children].forEach((dot, i) => i == page ? dot.setAttribute("aria-current", "true") : dot.removeAttribute("aria-current"));
		if (!isSettled) return;
		if (settled && [
			"index",
			"page",
			"pageCount"
		].every((key) => settled[key] == state[key])) return;
		const first = !settled;
		settled = state;
		if (first) return;
		root.dispatchEvent(new CustomEvent("sc:change", { detail: { ...state } }));
		options.onChange?.({ ...state });
		if (status && !silent && !quiet) {
			const [from, to] = visibleRange(pos, geo);
			if (from >= 0) status.textContent = labels.status(from + 1, to + 1, slides.length, slides);
		}
		quiet = false;
	}
	function armSettle() {
		clearTimeout(settleTimer);
		settleTimer = setTimeout(settle, "onscrollend" in window ? 400 : 120);
	}
	function settle() {
		clearTimeout(settleTimer);
		if (touching || busy) return;
		scrolling = false;
		target = -1;
		pos = readPos();
		run("settle");
		update(true);
	}
	function cancelFade() {
		clearTimeout(fadeTimer);
		track.removeAttribute("data-sc-fading");
	}
	function scrollToPos(position, how) {
		cancelFade();
		if (Math.abs(readPos() - position) < 1) return settle();
		if (how == "fade" && !motion.matches) {
			track.setAttribute("data-sc-fading", "");
			fadeTimer = setTimeout(() => {
				moveTo(position, "instant");
				track.removeAttribute("data-sc-fading");
			}, 160);
			return;
		}
		moveTo(position, how == "instant" || motion.matches ? "instant" : "smooth");
		armSettle();
	}
	function toPage(page, how) {
		if (!pages[page]) return;
		target = page;
		update();
		scrollToPos(pages[page].pos, how);
	}
	function step(direction, wrap, silent) {
		if (pages.length < 2) return;
		if (silent) quiet = true;
		const from = target >= 0 ? pages[target].pos : readPos();
		let next = -1;
		pages.forEach((page, i) => {
			if (direction > 0 ? next < 0 && page.pos > from + 2 : page.pos < from - 2) next = i;
		});
		if (next >= 0) toPage(next);
		else if (wrap) toPage(direction > 0 ? 0 : pages.length - 1, rewind == "fade" ? "fade" : void 0);
	}
	/** The user acted through a control: stop autoplay and let the move be announced. */
	function takeControl() {
		quiet = false;
		run("control");
	}
	function grab() {
		takeControl();
		cancelFade();
		target = -1;
	}
	const click = (button, direction) => listen(button, "click", () => {
		if (button.getAttribute("aria-disabled") == "true") return;
		takeControl();
		step(direction, !!rewind);
	});
	click(prevButton, -1);
	click(nextButton, 1);
	listen(dots, "click", (event) => {
		const dot = event.target.closest(".sc-dot");
		if (!dot) return;
		takeControl();
		toPage([...dots.children].indexOf(dot));
	});
	listen(track, "keydown", (event) => {
		if (event.target != track || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
		const key = event.key;
		const forward = rtl ? "ArrowLeft" : "ArrowRight";
		if (![
			forward,
			rtl ? "ArrowRight" : "ArrowLeft",
			"Home",
			"End"
		].includes(key)) return;
		event.preventDefault();
		takeControl();
		if (key == "Home") toPage(0);
		else if (key == "End") toPage(pages.length - 1);
		else step(key == forward ? 1 : -1, false);
	});
	listen(track, "scroll", () => {
		scrolling = true;
		frame ||= requestAnimationFrame(() => {
			frame = 0;
			pos = readPos();
			update();
		});
		armSettle();
	}, { passive: true });
	listen(track, "scrollend", settle);
	listen(track, "touchstart", () => {
		touching = true;
		grab();
	}, { passive: true });
	for (const type of ["touchend", "touchcancel"]) listen(track, type, () => {
		touching = false;
		armSettle();
	}, { passive: true });
	listen(track, "wheel", (event) => {
		if (Math.abs(event.deltaX) > Math.abs(event.deltaY)) grab();
	}, { passive: true });
	const resizes = new ResizeObserver(() => {
		measureFrame ||= requestAnimationFrame(() => {
			measureFrame = 0;
			measure();
			update(!scrolling, true);
		});
	});
	const observeSlides = () => {
		resizes.disconnect();
		for (const element of [track, ...slides]) resizes.observe(element);
	};
	const mutations = new MutationObserver(() => {
		const keep = !scrolling && target < 0 && !busy ? slides[state.index] : void 0;
		const seen = keep ? geo.starts[state.index] - pos : 0;
		measure();
		if (keep?.parentNode == track) {
			const wanted = clamp(geo.starts[slides.indexOf(keep)] - seen, 0, geo.max);
			if (Math.abs(readPos() - wanted) > 1) {
				moveTo(wanted, "instant");
				pos = readPos();
			}
		}
		observeSlides();
		update(!scrolling, true);
	});
	const context = {
		root,
		track,
		listen,
		layout: () => ({
			pages,
			max: geo.max,
			rtl,
			snap,
			state
		}),
		toPage,
		scrollTo: scrollToPos,
		step,
		grab,
		hold: (on) => busy = on,
		on: (event, handler) => hooks[event].push(handler)
	};
	const api = {
		root,
		track,
		get slides() {
			return slides;
		},
		get state() {
			return { ...state };
		},
		next() {
			takeControl();
			step(1, !!rewind);
		},
		prev() {
			takeControl();
			step(-1, !!rewind);
		},
		slideTo(index, { instant } = {}) {
			takeControl();
			toPage(pageOf(pages, clamp(index, 0, slides.length - 1)), instant ? "instant" : void 0);
		},
		goToPage(page, { instant } = {}) {
			takeControl();
			toPage(clamp(page, 0, pages.length - 1), instant ? "instant" : void 0);
		},
		update() {
			measure();
			update(!scrolling, true);
		},
		destroy() {
			listeners.abort();
			resizes.disconnect();
			mutations.disconnect();
			cancelAnimationFrame(frame);
			cancelAnimationFrame(measureFrame);
			clearTimeout(settleTimer);
			cancelFade();
			cleanups.forEach((cleanup) => cleanup?.());
			for (const name of [
				"ready",
				"overflow",
				"start",
				"end"
			]) root.removeAttribute(`data-sc-${name}`);
			for (const slide of track.children) slide.removeAttribute("data-sc-snap-off");
			if (tabIndex != null) track.setAttribute("tabindex", tabIndex);
			prevButton?.removeAttribute("aria-disabled");
			nextButton?.removeAttribute("aria-disabled");
			dots?.replaceChildren();
			instances.delete(root);
		}
	};
	for (const key of [
		"index",
		"page",
		"pageCount",
		"isBeginning",
		"isEnd"
	]) Object.defineProperty(api, key, { get: () => state[key] });
	measure();
	update(true, true);
	observeSlides();
	mutations.observe(track, { childList: true });
	const cleanups = (options.plugins || []).map((plugin) => {
		const { destroy, ...extra } = plugin(context) || {};
		Object.assign(api, extra);
		return destroy;
	});
	instances.set(root, api);
	root.setAttribute("data-sc-ready", "");
	return api;
}
//#endregion
export { attach, getCarousel };
