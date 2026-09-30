//#region src/history.ts
const KEY = "ssSheets";
const ENTRY = "ssEntry";
const MODE = "ssMode";
/** Tokens of the sheets that are open now, across all sheets using the plugin. */
const live = /* @__PURE__ */ new Set();
let listening = false;
/** Settles when a step back over a closed sheet's entry has arrived. */
let stepping = null;
const stepBack = () => {
	stepping = new Promise((resolve) => {
		const arrived = () => {
			stepping = null;
			resolve();
		};
		window.addEventListener("popstate", arrived, { once: true });
	});
	window.history.back();
};
const navigation = () => window.navigation;
const state = () => window.history.state;
const tokens = () => {
	const list = state()?.[KEY];
	return Array.isArray(list) ? list : [];
};
const write = (list, push) => {
	const next = {
		...state(),
		[KEY]: list,
		[ENTRY]: push ?? state()?.[ENTRY]
	};
	if (!push) return window.history.replaceState(next, "");
	const mode = window.history.scrollRestoration;
	window.history.replaceState({
		...state(),
		[MODE]: mode
	}, "");
	window.history.scrollRestoration = "manual";
	window.history.pushState({
		...next,
		[MODE]: void 0
	}, "");
	window.history.scrollRestoration = mode;
};
function neutralise() {
	const mode = state()?.[MODE];
	if (mode) window.history.scrollRestoration = mode;
	const list = tokens();
	const kept = list.filter((token) => live.has(token));
	if (kept.length != list.length) write(kept, null);
}
function history() {
	return (sheet) => {
		const { dialog } = sheet;
		let token = "";
		let entry = null;
		const push = () => {
			write([...tokens(), token], token);
			entry = {
				url: location.href,
				key: navigation()?.currentEntry?.key
			};
		};
		const ours = () => !!entry && state()?.[ENTRY] == token && location.href == entry.url && (entry.key == void 0 || navigation()?.currentEntry?.key == entry.key);
		const onOpen = () => {
			const mine = token = Math.random().toString(36).slice(2);
			live.add(token);
			if (stepping) stepping.then(() => token == mine && push());
			else push();
		};
		const onClose = (event) => {
			const own = ours();
			live.delete(token);
			token = "";
			entry = null;
			if (event.detail.reason != "history" && own) stepBack();
		};
		const onPop = () => {
			if (token && entry && !tokens().includes(token)) sheet.requestClose("history").then((closed) => closed || push());
		};
		dialog.addEventListener("ss:open", onOpen);
		dialog.addEventListener("ss:close", onClose);
		window.addEventListener("popstate", onPop);
		if (!listening) {
			listening = true;
			window.addEventListener("popstate", () => setTimeout(neutralise));
		}
		return () => {
			dialog.removeEventListener("ss:open", onOpen);
			dialog.removeEventListener("ss:close", onClose);
			window.removeEventListener("popstate", onPop);
			live.delete(token);
			token = "";
		};
	};
}
//#endregion
export { history };
