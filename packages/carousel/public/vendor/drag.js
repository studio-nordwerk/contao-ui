import { n as clamp, r as closest } from "./shared/math-DhvRXjtu.js";
//#region src/drag.ts
const drag = ({ threshold = 6 } = {}) => ({ root, track, listen, layout, toPage, scrollTo, grab, hold, on }) => {
	let suppressUntil = 0;
	let press = null;
	const readPos = () => Math.abs(track.scrollLeft);
	root.setAttribute("data-sc-drag", "");
	listen(track, "dragstart", (event) => event.preventDefault());
	listen(track, "click", (event) => {
		if (event.timeStamp > suppressUntil) return;
		event.preventDefault();
		event.stopPropagation();
	}, { capture: true });
	on("settle", () => track.removeAttribute("data-sc-dragging"));
	listen(track, "pointerdown", (event) => {
		if (event.pointerType != "mouse" || event.button || !layout().state.overflow) return;
		if (event.target.closest("input, textarea, select, label, [contenteditable], [data-sc-no-drag]")) return;
		press = {
			id: event.pointerId,
			x: event.clientX,
			left: track.scrollLeft,
			from: readPos(),
			moved: false,
			trail: [[event.timeStamp, event.clientX]]
		};
	});
	listen(document, "pointermove", (event) => {
		if (!press || event.pointerId != press.id) return;
		if (!(event.buttons & 1)) return release(event);
		const dx = event.clientX - press.x;
		if (!press.moved) {
			if (Math.abs(dx) < threshold) return;
			press.moved = true;
			grab();
			hold(true);
			track.setPointerCapture(press.id);
			track.setAttribute("data-sc-dragging", "");
			getSelection()?.removeAllRanges();
		}
		track.scrollLeft = press.left - dx;
		press.trail.push([event.timeStamp, event.clientX]);
		while (press.trail.length > 2 && event.timeStamp - press.trail[0][0] > 100) press.trail.shift();
	});
	const release = (event) => {
		const done = press;
		if (!done || event.pointerId != done.id) return;
		press = null;
		if (!done.moved) return;
		hold(false);
		suppressUntil = event.timeStamp + 100;
		const { pages, max, rtl, snap } = layout();
		const [t0, x0] = done.trail[0];
		const velocity = event.timeStamp > t0 ? (event.clientX - x0) / (event.timeStamp - t0) : 0;
		const here = readPos();
		const forward = rtl ? velocity : -velocity;
		if (snap == "none") return scrollTo(clamp(here + forward * 200, 0, max));
		const positions = pages.map((page) => page.pos);
		const start = closest(positions, done.from);
		let page = closest(positions, here);
		const moved = here - done.from;
		if (page == start && (Math.abs(moved) > 40 || Math.abs(forward) > .3)) page = clamp(start + Math.sign(Math.abs(moved) > 40 ? moved : forward), 0, pages.length - 1);
		toPage(page);
	};
	listen(document, "pointerup", release);
	listen(document, "pointercancel", release);
	return { destroy() {
		root.removeAttribute("data-sc-drag");
		track.removeAttribute("data-sc-dragging");
	} };
};
//#endregion
export { drag };
