//#region src/markup.ts
const PROPERTIES = {
	perView: "per-view",
	slideSize: "slide-size",
	gap: "gap",
	offsetBefore: "offset-before",
	offsetAfter: "offset-after",
	snap: "snap",
	align: "align",
	group: "group",
	controls: "controls"
};
const LENGTHS = /* @__PURE__ */ new Set([
	"gap",
	"offsetBefore",
	"offsetAfter"
]);
function value(key, raw) {
	if (key == "controls") return raw ? "initial" : "none";
	return typeof raw == "number" && LENGTHS.has(key) ? `${raw}px` : String(raw).replace(/[<>]/g, "");
}
const isResponsive = (raw) => typeof raw == "object" && raw != null;
/** Custom properties for the root's style attribute: plain values and the 0 breakpoint. */
function baseVars(props) {
	const vars = {};
	for (const [key, name] of Object.entries(PROPERTIES)) {
		const raw = props[key];
		const base = isResponsive(raw) ? raw[0] : raw;
		if (base !== void 0 && !(key == "controls" && base)) vars[`--sc-${name}`] = value(key, base);
	}
	if (props.centered) vars["--sc-centered"] = "1";
	return vars;
}
/** The same as a CSS declaration string, for templating languages. */
const baseStyle = (props) => Object.entries(baseVars(props)).map(([name, val]) => `${name}:${val}`).join(";");
/**
* Container-query rules for responsive values, scoped to [data-sc-id="id"]. Empty when nothing
* is responsive. Render it in a <style> element inside the root.
*/
function responsiveCss(id, props) {
	const rules = /* @__PURE__ */ new Map();
	for (const [key, name] of Object.entries(PROPERTIES)) {
		const raw = props[key];
		if (!isResponsive(raw)) continue;
		for (const [width, val] of Object.entries(raw)) {
			if (+width <= 0) continue;
			if (!rules.has(+width)) rules.set(+width, []);
			rules.get(+width).push(`--sc-${name}:${value(key, val)}`);
		}
	}
	return [...rules].sort(([a], [b]) => a - b).map(([width, decls]) => `@container sc (min-width:${width}px){[data-sc-id="${id}"]>*{${decls.join(";")}}}`).join("");
}
/**
* Inline script to render immediately after a carousel that opens on a later slide. It sets the
* start position before the first paint, so a deferred or module script finds it in place.
* Start alignment only. It expects the root as its previous sibling.
*/
const PRE_POSITION = "(function(r){var t=r.querySelector('[data-sc-track]'),s=t&&t.querySelector('[data-sc-initial]'),f=t&&t.firstElementChild;if(!s||s==f)return;var a=s.getBoundingClientRect(),b=f.getBoundingClientRect();t.scrollLeft=getComputedStyle(t).direction=='rtl'?a.right-b.right:a.left-b.left})(document.currentScript.previousElementSibling)";
/**
* Label templates such as 'Slide {n} of {count}' turned into label functions. `statusSingle`
* is used when only one slide is visible, e.g. 'Item {first} of {count}' next to a `status` of
* 'Items {first} to {last} of {count}'.
*/
function templateLabels(templates = {}) {
	const fill = (template, values) => template.replace(/\{(\w+)\}/g, (match, name) => name in values ? String(values[name]) : match);
	const status = templates.status || templates.statusSingle;
	return {
		...templates.page && { page: (n, count) => fill(templates.page, {
			n,
			count
		}) },
		...status && { status: (first, last, count) => fill(first == last && templates.statusSingle ? templates.statusSingle : status, {
			first,
			last,
			count
		}) }
	};
}
//#endregion
export { PRE_POSITION, baseStyle, baseVars, responsiveCss, templateLabels };
