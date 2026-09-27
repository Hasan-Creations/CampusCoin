// Campus Coin Accessibility & Theme Helpers
window.CampusCoin = {
    toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        return isDark ? 'dark' : 'light';
    },
    setFontSize(size) {
        const value = ['small', 'normal', 'large'].includes(size) ? size : 'normal';
        document.documentElement.dataset.fontSize = value;
        try {
            localStorage.setItem('fontSize', value);
        } catch (e) {}
        return value;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const control = document.getElementById('text-size-control');
    if (control) {
        control.value = document.documentElement.dataset.fontSize || 'normal';
    }
});

