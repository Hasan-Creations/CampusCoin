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

document.addEventListener('DOMContentLoaded', () => {
    if (window.Alpine) {
        return;
    }

    document.querySelectorAll('[aria-label="Adjust text scaling size"]').forEach((control) => {
        const wrapper = control.closest('[x-data]');
        const menu = wrapper?.querySelector('[role="menu"]');

        if (!wrapper || !menu) {
            return;
        }

        control.addEventListener('click', () => {
            const isOpen = menu.hidden;
            menu.hidden = !isOpen;
            control.setAttribute('aria-expanded', String(isOpen));
        });

        menu.querySelectorAll('[role="menuitem"]').forEach((item) => {
            item.addEventListener('click', () => {
                const size = item.textContent.toLowerCase().includes('x-large')
                    ? 'xlarge'
                    : item.textContent.toLowerCase().includes('large')
                        ? 'large'
                        : 'normal';

                window.CampusCoin.setFontSize(size);
                menu.hidden = true;
                control.setAttribute('aria-expanded', 'false');
            });
        });

        menu.hidden = true;
    });
});

