import { attach, getSheet } from "./vendor/index.js";

export async function enhanceSheets(scope = document) {
  for (const dialog of scope.querySelectorAll("dialog[data-nw-sheet]")) {
    if (getSheet(dialog)) continue;
    const options = JSON.parse(dialog.dataset.nwSheet || "{}");
    const plugins = [];
    if (options.history) plugins.push((await import("./vendor/history.js")).history());
    if (options.drag) plugins.push((await import("./vendor/drag.js")).drag());
    if (dialog.isConnected && !getSheet(dialog)) attach(dialog, { plugins });
  }
}

await enhanceSheets();
