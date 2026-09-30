const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
/** Index of the value closest to `target`; the earliest one wins a tie. */
const closest = (values, target) => {
	let best = 0;
	for (let i = 1; i < values.length; i++) if (Math.abs(values[i] - target) < Math.abs(values[best] - target) - .5) best = i;
	return best;
};
/** The scroll position at which each slide is aligned, clamped to the scroll range. */
const snapPositions = ({ starts, sizes, port, padStart, padEnd, max, align }) => starts.map((start, i) => clamp(align === "center" ? start + sizes[i] / 2 - padStart - (port - padStart - padEnd) / 2 : align === "end" ? start + sizes[i] - port + padEnd : start - padStart, 0, max));
/**
* The first slide of every page. Pages are counted outwards from `anchor` (the initial slide),
* so the initial slide always starts a page and the first page may be shorter than the rest.
* `group` is a number of slides, or 'page' for as many whole slides as fit in the view.
*/
const pageStarts = (geo, snaps, group, anchor) => {
	const count = snaps.length;
	const firsts = /* @__PURE__ */ new Set([0, anchor]);
	if (group === "page") {
		const fits = (from, last) => geo.starts[last] + geo.sizes[last] <= snaps[from] + geo.port - geo.padEnd + 2;
		for (let i = anchor; i < count && snaps[i] < geo.max - 2;) {
			let next = i + 1;
			while (next < count && fits(i, next)) next++;
			if (next >= count) break;
			firsts.add(i = next);
		}
		for (let i = anchor; i > 0;) {
			let first = i - 1;
			while (first > 0 && fits(first - 1, i - 1)) first--;
			firsts.add(i = first);
		}
	} else for (let i = anchor % group; i < count; i += group) firsts.add(i);
	return [...firsts].filter((i) => i >= 0 && i < count).sort((a, b) => a - b);
};
/**
* Pages at distinct positions, always ending at the end of the scroll range. A page's `first` is
* the earliest slide aligned at its position, which is also the index reported there: at the
* clamped end that is the first slide of the last full view, not the start of the last group.
*/
const buildPages = (geo, snaps, group, anchor) => {
	const pages = [];
	const add = (pos) => {
		const first = snaps.findIndex((snap) => snap >= pos - 2);
		pages.push({
			pos,
			first: first < 0 ? snaps.length - 1 : first
		});
	};
	for (const start of pageStarts(geo, snaps, group, anchor)) if (!pages.length || snaps[start] > pages[pages.length - 1].pos + 2) add(snaps[start]);
	if (pages.length && pages[pages.length - 1].pos < geo.max - 2) add(geo.max);
	return pages;
};
/** The page that contains slide `index`. */
const pageOf = (pages, index) => {
	let page = 0;
	pages.forEach((candidate, i) => {
		if (candidate.first <= index) page = i;
	});
	return page;
};
/** First and last slide that are at least half visible at `pos`, or [-1, -1]. */
const visibleRange = (pos, { starts, sizes, port }) => {
	let first = -1;
	let last = -1;
	starts.forEach((start, i) => {
		if (Math.min(start + sizes[i], pos + port) - Math.max(start, pos) >= sizes[i] / 2 - .5) {
			if (first < 0) first = i;
			last = i;
		}
	});
	return [first, last];
};
//#endregion
export { snapPositions as a, pageOf as i, clamp as n, visibleRange as o, closest as r, buildPages as t };
