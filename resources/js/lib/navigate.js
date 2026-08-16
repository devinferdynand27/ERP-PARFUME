import { mountIn, unmountIn } from '@/lib/mount';

const MAIN_SELECTOR = 'main[data-page-content]';

function isInternalNavigableLink(anchor) {
    if (!anchor || anchor.target || anchor.hasAttribute('download')) return false;
    if (anchor.hasAttribute('data-no-navigate')) return false;

    const url = new URL(anchor.href, window.location.origin);
    if (url.origin !== window.location.origin) return false;
    if (url.pathname === window.location.pathname && url.hash) return false;

    return true;
}

async function swapTo(url, { pushState = true } = {}) {
    const currentMain = document.querySelector(MAIN_SELECTOR);
    if (!currentMain) {
        window.location.href = url;
        return;
    }

    let response;
    let html;
    try {
        response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        html = await response.text();
    } catch (e) {
        window.location.href = url;
        return;
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const newMain = doc.querySelector(MAIN_SELECTOR);
    if (!newMain) {
        window.location.href = url;
        return;
    }

    unmountIn(currentMain);
    document.title = doc.title;
    currentMain.replaceWith(newMain);
    mountIn(newMain);

    if (pushState) {
        window.history.pushState({ navigated: true }, '', url);
    }

    window.dispatchEvent(new CustomEvent('app:navigated', {
        detail: { url, active: newMain.dataset.activeMenu ?? '' },
    }));
    window.scrollTo(0, 0);
}

export function initNavigate() {
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const anchor = event.target.closest('a');
        if (!isInternalNavigableLink(anchor)) return;

        event.preventDefault();
        if (anchor.href === window.location.href) return;

        swapTo(anchor.href);
    });

    window.addEventListener('popstate', () => {
        swapTo(window.location.href, { pushState: false });
    });
}
