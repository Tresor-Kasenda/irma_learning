import "./bootstrap";
import {initScrollToTop} from "./scroll-to-top";

import {Dropdown} from "@flexilla/dropdown";

import {$, $$} from "@flexilla/utilities";
import {Collapse} from "@flexilla/collapse";

import {notificationSystem} from "./utilities/notification";

import confetti from "canvas-confetti";
import {Accordion} from "@flexilla/flexilla";

window.notificationSystem = notificationSystem;
const initAllScript = () => {
    window.confetti = confetti;
    Dropdown.autoInit("[data-ui-dropdown]");
    Accordion.autoInit("[data-ui-accordion]");
    const collaspibles = $$("[data-ui-collapsible]");
    if (collaspibles && collaspibles.length > 0) {
        for (const collapsible of collaspibles) {
            new Collapse(collapsible, {defaultState: "open"});
        }
    }
    initScrollToTop();
    initChapiterSidebar();
};

document.addEventListener("livewire:navigated", initAllScript);
const initChapiterSidebar = () => {
    const sidebarChap = $("[data-slid-chapter]");
    const triggerEl = $("[data-trigger-slid-chapter]");
    const overlayEl = $("[data-overlay-slid-chapter]");
    if (sidebarChap && triggerEl) {
        const toggleState = () => {
            const isOpen = sidebarChap.getAttribute("data-state") === "open";
            sidebarChap.setAttribute("data-state", isOpen ? "close" : "open");
            overlayEl.setAttribute("data-state", isOpen ? "close" : "open");
        };
        const closeSlide = () => {
            sidebarChap.setAttribute("data-state", "close");
            overlayEl.setAttribute("data-state", "close");
        };
        triggerEl.addEventListener("click", toggleState);
        overlayEl?.addEventListener("click", closeSlide);
    }
};
