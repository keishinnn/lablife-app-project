// file path = public/assets/js/utils.js

/**
 * Toggle loading state on a button
 * @param {HTMLElement} button - The button element
 * @param {HTMLElement} form - The form element 
 * @param {boolean} isLoading - Whether to show loading state
 * @param {string} defaultText - Button text when not loading
 */
export function setLoadingState(button, isLoading, defaultText = "Submit") {
    if (!button) return;

    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent; // store old text
        button.textContent = "Loading...";
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || defaultText;
    }
}
