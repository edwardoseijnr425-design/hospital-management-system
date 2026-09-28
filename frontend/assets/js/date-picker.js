/* EHMS — custom date-picker bridge (legacy stub, recovered build).
   The old shell calls initCustomDatePickers(pageContent) after loading
   each page; pages use native <input type="date"> so nothing custom
   is required. Kept as a no-op for shell compatibility. */
window.initCustomDatePickers = function (root) {
    // no-op: native date inputs are used throughout the recovered build
    return root;
};