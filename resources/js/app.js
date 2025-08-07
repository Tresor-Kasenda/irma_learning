import "./bootstrap";
import { initScrollToTop } from "./scroll-to-top";

import { Dropdown } from "@flexilla/dropdown";

import { $, $$, toggleNavbar } from "@flexilla/utilities";
import { Collapse } from "@flexilla/collapse";

import { notificationSystem } from "./utilities/notification";

import confetti from "canvas-confetti";
import { Accordion, Tabs } from "@flexilla/flexilla";
import PluginTabs from "@flexilla/alpine-tabs";

window.notificationSystem = notificationSystem;
const initAllScript = () => {
    window.confetti = confetti;
    Dropdown.autoInit("[data-ui-dropdown]");
    Accordion.autoInit("[data-ui-accordion]");
    const collaspibles = $$("[data-ui-collapsible]");
    if (collaspibles && collaspibles.length > 0) {
        for (const collapsible of collaspibles) {
            new Collapse(collapsible, { defaultState: "open" });
        }
    }
    initScrollToTop();
    const navbarEl = document.querySelector("[data-main-navbar]");
    if (navbarEl instanceof HTMLElement) {
        toggleNavbar({ navbarElement: navbarEl });
    }
    const navDash = document.querySelector("[data-dash-nav]");
    if (navDash instanceof HTMLElement) {
        toggleNavbar({ navbarElement: navDash });
    }

    initChapiterSidebar();
    const tabResults = document.querySelector("[data-results-tabs]");
    if (tabResults instanceof HTMLElement) {
        new Tabs(tabResults);
    }
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

document.addEventListener("livewire:initialized", () => {
    Livewire.on("urlChanged", (params) => {
        window.history.pushState(null, "", params.url);
    });
});

Alpine.plugin(PluginTabs)