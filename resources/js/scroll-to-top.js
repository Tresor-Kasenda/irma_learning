export const initScrollToTop = () => { 
    const button = document.querySelector("[data-scroll-to-top]");
    if (button instanceof HTMLElement) {
        const handleScroll = () => {
            button.setAttribute("data-state", window.scrollY > 300 ? "visible" : "hidden");
        };
        const scrollToTop = () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        };
        window.addEventListener("scroll", handleScroll);
        button.addEventListener("click", scrollToTop);
    }
}