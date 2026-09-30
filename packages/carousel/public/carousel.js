import { attach, getCarousel } from "./vendor/index.js";
import { templateLabels } from "./vendor/markup.js";

export async function enhanceCarousels(scope = document) {
  for (const root of scope.querySelectorAll("[data-nw-carousel]")) {
    if (getCarousel(root)) continue;
    const options = JSON.parse(root.dataset.nwCarousel || "{}");
    const plugins = [];
    if (options.drag) plugins.push((await import("./vendor/drag.js")).drag());
    if (options.autoplay) {
      const { autoplay } = await import("./vendor/autoplay.js");
      plugins.push(autoplay({ delay: options.autoplay, labels: options.labels }));
    }
    if (root.isConnected && !getCarousel(root)) {
      attach(root, {
        initial: options.initial ?? 0,
        rewind: options.rewind ?? false,
        labels: templateLabels(options.labels),
        plugins,
      });
    }
  }
}

await enhanceCarousels();
