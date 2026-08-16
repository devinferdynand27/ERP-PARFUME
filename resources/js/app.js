import { mountIn } from '@/lib/mount';
import { initNavigate } from '@/lib/navigate';

document.addEventListener('DOMContentLoaded', () => {
    mountIn(document);
    initNavigate();
});
