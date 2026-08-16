const PALETTE = [
    'bg-slate-800 text-slate-50',
    'bg-rose-600 text-rose-50',
    'bg-amber-600 text-amber-50',
    'bg-emerald-600 text-emerald-50',
    'bg-sky-600 text-sky-50',
    'bg-violet-600 text-violet-50',
    'bg-fuchsia-600 text-fuchsia-50',
];

export function initialOf(name) {
    return (name?.trim()?.[0] ?? '?').toUpperCase();
}

export function colorFor(name) {
    const str = name ?? '';
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
    }
    return PALETTE[hash % PALETTE.length];
}
