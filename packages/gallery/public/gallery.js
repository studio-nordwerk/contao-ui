import { enhanceCarousels } from "../nordwerkcarousel/carousel.js";
import { getCarousel } from "../nordwerkcarousel/vendor/index.js";
import { enhanceSheets } from "../nordwerksheet/sheet.js";
import { getSheet } from "../nordwerksheet/vendor/index.js";

await Promise.all([enhanceCarousels(), enhanceSheets()]);

for (const gallery of document.querySelectorAll("[data-nw-gallery]")) {
  const dialog = gallery.querySelector("dialog.nw-gallery-lightbox");
  const root = dialog?.querySelector("[data-nw-carousel]");
  const sheet = dialog && getSheet(dialog);
  const carousel = root && getCarousel(root);
  if (!sheet || !carousel) continue;
  const track = root.querySelector("[data-sc-track]");
  const slides = [...track.children];
  // Keep off-screen full images out of the accessibility tree. Native scrolling stays intact.
  const revealCurrent = () => {
    for (const [index, slide] of slides.entries()) {
      slide.toggleAttribute("inert", index !== carousel.index);
      if (index !== carousel.index) slide.setAttribute("aria-hidden", "true");
      else slide.removeAttribute("aria-hidden");
    }
  };
  root.addEventListener("sc:change", revealCurrent);
  dialog.addEventListener("keydown", (event) => {
    if (event.target.closest("input, textarea, select, [contenteditable]")) return;
    if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return;
    // The original carousel already handles events from its track.
    if (track.contains(event.target)) return;
    event.preventDefault();
    track.dispatchEvent(new KeyboardEvent("keydown", { key: event.key, bubbles: true }));
  });
  gallery.addEventListener("click", (event) => {
    const link = event.target.closest("a[data-nw-gallery-open]");
    if (
      !link ||
      !gallery.contains(link) ||
      event.defaultPrevented ||
      event.button ||
      event.metaKey ||
      event.ctrlKey ||
      event.shiftKey ||
      event.altKey
    )
      return;
    const index = slides.findIndex(
      (slide) => slide.dataset.nwGalleryIndex === link.dataset.nwGalleryOpen,
    );
    if (index < 0) return;
    event.preventDefault();
    sheet.open({ invoker: link });
    carousel.update();
    carousel.slideTo(index, { instant: true });
    revealCurrent();
  });
  gallery.setAttribute("data-nw-gallery-ready", "");
}
