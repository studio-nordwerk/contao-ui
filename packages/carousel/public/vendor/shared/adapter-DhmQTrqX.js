import { attach } from "../index.js";
import { PRE_POSITION, baseVars, responsiveCss } from "../markup.js";
//#region src/adapter.ts
const chevron = (h, path) => h("svg", {
	viewBox: "0 0 24 24",
	"aria-hidden": "true",
	focusable: "false"
}, h("path", { d: path }));
function createAdapter(framework) {
	const { createElement: h, Fragment, useState, useRef, useEffect, useLayoutEffect, useId, toChildArray } = framework;
	const useBeforePaint = typeof window == "undefined" ? useEffect : useLayoutEffect;
	/**
	* Attach a carousel to your own markup. Put `ref` on the root. Options are read once, when
	* the root mounts; give the component a new `key` to attach again with other options.
	*/
	function useCarousel(options = {}) {
		const ref = useRef(null);
		const latest = useRef(options);
		useBeforePaint(() => {
			latest.current = options;
		});
		const [carousel, setCarousel] = useState(null);
		const [state, setState] = useState(null);
		useBeforePaint(() => {
			if (!ref.current) return;
			const instance = attach(ref.current, {
				...latest.current,
				onChange: (next) => {
					setState(next);
					latest.current.onChange?.(next);
				}
			});
			setCarousel(instance);
			setState(instance.state);
			return () => {
				instance.destroy();
				setCarousel(null);
			};
		}, []);
		return {
			ref,
			carousel,
			state
		};
	}
	function Carousel(props) {
		const id = useId();
		const { as = "div", label, children, className, style, slideClassName, slideRoles, centerFew } = props;
		const { arrows = true, dots = true, playButton, labels = {}, initial = 0, carouselRef } = props;
		const { ref, carousel } = useCarousel({
			group: typeof props.group == "object" ? void 0 : props.group,
			initial,
			rewind: props.rewind,
			labels,
			plugins: props.plugins,
			onChange: props.onChange
		});
		const handoff = useRef(carouselRef);
		useEffect(() => {
			handoff.current = carouselRef;
		});
		useEffect(() => {
			if (!carousel) return;
			handoff.current?.(carousel);
			return () => handoff.current?.(null);
		}, [carousel]);
		const slides = toChildArray(children);
		const item = as == "ul" ? "li" : "div";
		const css = responsiveCss(id, props);
		const root = h("div", {
			ref,
			className: [
				"sc",
				centerFew && "sc--center-few",
				className
			].filter(Boolean).join(" "),
			"data-sc-id": id,
			style: {
				...baseVars(props),
				...style
			}
		}, css && h("style", { dangerouslySetInnerHTML: { __html: css } }), h(as, {
			className: "sc-track",
			"data-sc-track": "",
			tabIndex: 0,
			"aria-label": label
		}, slides.map((child, i) => h(item, {
			key: child?.key ?? i,
			className: slideClassName,
			"data-sc-initial": i == initial && i > 0 ? "" : void 0,
			...slideRoles && {
				role: "group",
				"aria-roledescription": "slide",
				"aria-label": `${i + 1} of ${slides.length}`
			}
		}, child))), arrows && h(Fragment, null, h("button", {
			type: "button",
			className: "sc-nav sc-prev",
			"data-sc-prev": "",
			"aria-label": labels.prev || "Previous"
		}, chevron(h, "m15 18-6-6 6-6")), h("button", {
			type: "button",
			className: "sc-nav sc-next",
			"data-sc-next": "",
			"aria-label": labels.next || "Next"
		}, chevron(h, "m9 6 6 6-6 6"))), playButton && h("button", {
			type: "button",
			className: "sc-play",
			"data-sc-play": "",
			"aria-label": "Stop automatic scrolling"
		}, h("svg", {
			className: "sc-icon-play",
			viewBox: "0 0 24 24",
			"aria-hidden": "true",
			focusable: "false"
		}, h("path", { d: "M8 5v14l11-7z" })), h("svg", {
			className: "sc-icon-pause",
			viewBox: "0 0 24 24",
			"aria-hidden": "true",
			focusable: "false"
		}, h("path", { d: "M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" }))), dots && h("div", {
			className: "sc-dots",
			"data-sc-dots": ""
		}), h("p", {
			className: "sc-status",
			"data-sc-status": "",
			"aria-live": "polite"
		}));
		return initial > 0 ? h(Fragment, null, root, h("script", { dangerouslySetInnerHTML: { __html: PRE_POSITION } })) : root;
	}
	return {
		Carousel,
		useCarousel
	};
}
//#endregion
export { createAdapter as t };
