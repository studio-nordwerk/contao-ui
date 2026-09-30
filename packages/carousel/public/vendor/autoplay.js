//#region src/autoplay.ts
const autoplay = ({ delay = 5e3, start = true, button, labels = {} } = {}) => ({ root, track, listen, layout, step, on }) => {
	const play = button || root.querySelector("[data-sc-play]");
	const holds = /* @__PURE__ */ new Set();
	let playing = start && !matchMedia("(prefers-reduced-motion: reduce)").matches;
	let timer = 0;
	let left = delay;
	let since = 0;
	function sync() {
		const running = playing && !holds.size && layout().pages.length > 1;
		root.toggleAttribute("data-sc-playing", playing);
		root.toggleAttribute("data-sc-paused", playing && !running);
		play?.setAttribute("aria-label", playing ? labels.pause || "Stop automatic scrolling" : labels.play || "Start automatic scrolling");
		if (running && !timer) {
			since = performance.now();
			timer = setTimeout(() => {
				timer = 0;
				left = delay;
				step(1, true, true);
				sync();
			}, left);
		} else if (!running && timer) {
			clearTimeout(timer);
			timer = 0;
			left = Math.max(0, left - (performance.now() - since));
		}
	}
	function set(on) {
		playing = on;
		left = delay;
		clearTimeout(timer);
		timer = 0;
		sync();
	}
	const hold = (reason, on) => {
		holds[on ? "add" : "delete"](reason);
		sync();
	};
	if (track.matches(":hover")) holds.add("hover");
	if (document.hidden) holds.add("hidden");
	if (root.contains(document.activeElement) && !play?.contains(document.activeElement)) playing = false;
	root.style.setProperty("--sc-autoplay-delay", `${delay}ms`);
	on("control", () => playing && set(false));
	on("measure", sync);
	listen(play, "click", () => set(!playing));
	listen(track, "pointerenter", (event) => event.pointerType == "mouse" && hold("hover", true));
	listen(track, "pointerleave", (event) => event.pointerType == "mouse" && hold("hover", false));
	listen(root, "focusin", (event) => !play?.contains(event.target) && playing && set(false));
	listen(document, "visibilitychange", () => hold("hidden", document.hidden));
	const visibility = new IntersectionObserver(([entry]) => hold("offscreen", !entry.isIntersecting));
	visibility.observe(root);
	sync();
	return {
		play: () => set(true),
		pause: () => set(false),
		destroy() {
			clearTimeout(timer);
			visibility.disconnect();
			root.removeAttribute("data-sc-playing");
			root.removeAttribute("data-sc-paused");
			root.style.removeProperty("--sc-autoplay-delay");
		}
	};
};
//#endregion
export { autoplay };
