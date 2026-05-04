document.addEventListener("DOMContentLoaded", function () {
    var elemenMunculSaatScroll = document.querySelectorAll(".muncul-saat-scroll");

    if (!elemenMunculSaatScroll.length) {
        return;
    }

    document.documentElement.classList.add("animasi-scroll-siap");

    var urutanCard = 0;

    elemenMunculSaatScroll.forEach(function (elemen) {
        if (elemen.classList.contains("card")) {
            elemen.style.setProperty("--reveal-delay", Math.min(urutanCard * 70, 350) + "ms");
            urutanCard++;
        }
    });

    if (!("IntersectionObserver" in window)) {
        elemenMunculSaatScroll.forEach(function (elemen) {
            elemen.classList.add("sudah-terlihat");
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add("sudah-terlihat");
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.25,
        rootMargin: "0px 0px -100px 0px"
    });

    elemenMunculSaatScroll.forEach(function (elemen) {
        observer.observe(elemen);
    });
});
