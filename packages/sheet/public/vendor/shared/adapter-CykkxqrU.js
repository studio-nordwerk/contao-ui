import { attach } from "../index.js";
//#region src/adapter.ts
const join = (...names) => names.filter(Boolean).join(" ");
function createAdapter(framework) {
	const { createElement: h, createContext, useContext, useState, useRef, useEffect, useId } = framework;
	const SheetId = createContext("");
	/**
	* Attaches the core to a dialog you render yourself: put `ref` on the <dialog class="ss">.
	* Takes the state options of <Sheet> (open, defaultOpen, onOpenChange, onSnap, replace, plugins,
	* sheetRef); plugins and replace are read once, when the dialog mounts.
	*/
	function useSheet(options = {}) {
		const ref = useRef(null);
		const [sheet, setSheet] = useState(null);
		const latest = useRef(options);
		useEffect(() => {
			latest.current = options;
		});
		const closingFromProp = useRef(false);
		const closeFromProp = (instance) => {
			closingFromProp.current = true;
			instance.requestClose("api").finally(() => closingFromProp.current = false);
		};
		useEffect(() => {
			const dialog = ref.current;
			const instance = attach(dialog, {
				plugins: latest.current.plugins,
				replace: latest.current.replace
			});
			const controlled = () => latest.current.open !== void 0;
			let frame = 0;
			const reconcile = () => {
				cancelAnimationFrame(frame);
				frame = requestAnimationFrame(() => {
					if (!controlled()) return;
					const want = latest.current.open;
					if (want && !dialog.open) instance.open();
					else if (!want && dialog.open) closeFromProp(instance);
				});
			};
			const onOpen = () => {
				latest.current.onOpenChange?.(true, { reason: "open" });
				reconcile();
			};
			const onRequest = (event) => {
				if (!controlled() || closingFromProp.current) return;
				event.preventDefault();
				latest.current.onOpenChange?.(false, { reason: event.detail.reason });
			};
			const onClose = (event) => {
				if (closingFromProp.current) return;
				latest.current.onOpenChange?.(false, { reason: event.detail.reason });
				reconcile();
			};
			const onSnap = () => latest.current.onSnap?.(instance.state);
			dialog.addEventListener("ss:open", onOpen);
			dialog.addEventListener("ss:requestclose", onRequest);
			dialog.addEventListener("ss:close", onClose);
			dialog.addEventListener("ss:snap", onSnap);
			setSheet(instance);
			latest.current.sheetRef?.(instance);
			if (dialog.open) onOpen();
			else if (latest.current.defaultOpen) instance.open();
			return () => {
				cancelAnimationFrame(frame);
				dialog.removeEventListener("ss:open", onOpen);
				dialog.removeEventListener("ss:requestclose", onRequest);
				dialog.removeEventListener("ss:close", onClose);
				dialog.removeEventListener("ss:snap", onSnap);
				instance.destroy();
				latest.current.sheetRef?.(null);
				setSheet(null);
			};
		}, []);
		const { open } = options;
		useEffect(() => {
			if (!sheet || open === void 0) return;
			if (open) sheet.open();
			else if (sheet.dialog.open) closeFromProp(sheet);
		}, [sheet, open]);
		return {
			ref,
			sheet
		};
	}
	function Sheet(props) {
		const generated = useId();
		const id = props.id ?? `ss${generated.replace(/[^\w-]/g, "")}`;
		const { presentation, depth, label, snapPoints = [], initialSnap, className, panelClassName, style } = props;
		const { ref } = useSheet(props);
		return h(SheetId.Provider, { value: id }, h("dialog", {
			ref,
			id,
			className: join("ss", className),
			"data-ss": presentation,
			"data-ss-depth": depth ? "" : void 0,
			"aria-label": label,
			"aria-labelledby": label ? void 0 : `${id}-title`,
			style,
			suppressHydrationWarning: true
		}, h("div", { className: join("ss-panel", panelClassName) }, snapPoints.map((at, index) => h("i", {
			key: at,
			className: "ss-snap",
			style: { "--ss-at": at },
			"data-ss-initial": index === initialSnap ? "" : void 0
		})), props.children), h("div", { className: "ss-rest" })));
	}
	const part = (tag, name, extra, fallback) => ({ className, children, ...rest }) => {
		const id = useContext(SheetId);
		return h(tag, {
			...extra?.(id),
			...rest,
			className: join(name, className)
		}, children ?? fallback);
	};
	/** A button anywhere on the page that opens the sheet with the given id. */
	function SheetTrigger({ sheet, children, ...rest }) {
		return h("button", {
			type: "button",
			commandfor: sheet,
			command: "show-modal",
			...rest
		}, children);
	}
	return {
		Sheet,
		useSheet,
		SheetTrigger,
		SheetClose: part("button", "ss-close", (id) => ({
			type: "button",
			commandfor: id,
			command: "close",
			"aria-label": "Close"
		})),
		SheetTitle: part("h2", "ss-title", (id) => ({ id: `${id}-title` })),
		SheetHandle: part("button", "ss-handle", (id) => ({
			type: "button",
			commandfor: id,
			command: "--ss-cycle",
			"aria-label": "Change height"
		})),
		SheetHeader: part("header", "ss-header"),
		SheetBody: part("div", "ss-body"),
		SheetFooter: part("footer", "ss-footer")
	};
}
//#endregion
export { createAdapter as t };
