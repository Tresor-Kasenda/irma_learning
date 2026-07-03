export async function readVideoDurationMinutes(file: File | null): Promise<number | null> {
    if (! file || ! file.type.startsWith('video/')) {
        return null;
    }

    return new Promise((resolve) => {
        const video = document.createElement('video');
        const objectUrl = URL.createObjectURL(file);
        const cleanup = () => {
            URL.revokeObjectURL(objectUrl);
            video.removeAttribute('src');
            video.load();
        };
        const timeout = window.setTimeout(() => {
            cleanup();
            resolve(null);
        }, 10_000);

        video.preload = 'metadata';
        video.onloadedmetadata = () => {
            window.clearTimeout(timeout);
            const minutes = Number.isFinite(video.duration) && video.duration > 0
                ? Math.max(1, Math.ceil(video.duration / 60))
                : null;
            cleanup();
            resolve(minutes);
        };
        video.onerror = () => {
            window.clearTimeout(timeout);
            cleanup();
            resolve(null);
        };
        video.src = objectUrl;
    });
}
