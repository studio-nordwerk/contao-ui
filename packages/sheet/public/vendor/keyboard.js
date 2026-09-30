//#region src/keyboard.ts
function keyboard() {
	return (sheet) => {
		const { dialog } = sheet;
		const viewport = window.visualViewport;
		if (!viewport) return;
		let saved = null;
		let frame = 0;
		let placed = "";
		let snap = "full";
		const reveal = () => {
			const field = document.activeElement;
			if (!field || field == dialog || !dialog.contains(field)) return;
			const box = field.closest(".ss-body");
			if (!box) return;
			const footer = box.parentElement?.querySelector(":scope > .ss-footer");
			const area = box.getBoundingClientRect();
			const frameBox = dialog.getBoundingClientRect();
			const bottom = Math.min(area.bottom, frameBox.bottom, footer?.getBoundingClientRect().top ?? Infinity);
			const top = Math.max(area.top, frameBox.top);
			const rect = field.getBoundingClientRect();
			if (rect.bottom > bottom) box.scrollTop += rect.bottom - bottom + 16;
			else if (rect.top < top) box.scrollTop -= top - rect.top + 16;
		};
		const update = () => {
			cancelAnimationFrame(frame);
			frame = requestAnimationFrame(() => {
				if (!dialog.open || viewport.scale > 1.01) return;
				if (saved && (scrollX != saved[0] || scrollY != saved[1])) scrollTo(saved[0], saved[1]);
				const top = Math.round(viewport.offsetTop);
				const height = Math.round(viewport.height);
				const moved = top > 0 || height < innerHeight - 1;
				const next = moved ? `${top} ${height}` : "";
				if (next != placed) {
					placed = next;
					if (moved) {
						dialog.style.setProperty("--ss-viewport-top", `${top}px`);
						dialog.style.setProperty("--ss-viewport-height", `${height}px`);
					} else {
						dialog.style.removeProperty("--ss-viewport-top");
						dialog.style.removeProperty("--ss-viewport-height");
					}
					sheet.snapTo(snap, { instant: true });
				}
				reveal();
			});
		};
		const onSnap = (event) => {
			const { snap: index, snapPoints } = event.detail;
			snap = index == snapPoints.length - 1 ? "full" : index;
		};
		const onFocus = () => update();
		const onOpen = (event) => {
			saved = [scrollX, scrollY];
			snap = event.detail.snap ?? "full";
			dialog.addEventListener("ss:snap", onSnap);
			dialog.addEventListener("focusin", onFocus);
			viewport.addEventListener("resize", update);
			viewport.addEventListener("scroll", update);
			update();
		};
		const onClose = () => {
			placed = "";
			dialog.removeEventListener("ss:snap", onSnap);
			dialog.removeEventListener("focusin", onFocus);
			viewport.removeEventListener("resize", update);
			viewport.removeEventListener("scroll", update);
			cancelAnimationFrame(frame);
			dialog.style.removeProperty("--ss-viewport-top");
			dialog.style.removeProperty("--ss-viewport-height");
			if (saved && (scrollX != saved[0] || scrollY != saved[1])) scrollTo(saved[0], saved[1]);
			saved = null;
		};
		dialog.addEventListener("ss:open", onOpen);
		dialog.addEventListener("ss:close", onClose);
		return () => {
			onClose();
			dialog.removeEventListener("ss:open", onOpen);
			dialog.removeEventListener("ss:close", onClose);
		};
	};
}
//#endregion
export { keyboard };
