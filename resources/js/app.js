// Campus Coin Accessibility & Theme Helpers
window.CampusCoin = {
    toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        return isDark ? 'dark' : 'light';
    }
};

