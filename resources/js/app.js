// Campus Coin Accessibility & Theme Helpers
window.CampusCoin = {
    toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        return isDark ? 'dark' : 'light';
    },
    setFontSize(size) {
        if (['normal', 'large', 'xlarge'].includes(size)) {
            document.documentElement.setAttribute('data-font-size', size);
            localStorage.setItem('font-size', size);
        }
    },
    getFontSize() {
        return localStorage.getItem('font-size') || 'normal';
    }
};
