<div class="col-6 col-md-4 col-lg-3 song-card-<?= $song['id'] ?>"> <!-- Más compacta -->
    <div class="song-card shadow-sm rounded-3 overflow-hidden position-relative">

        <!-- Imagen con Overlay -->
        <div class="position-relative">
            <div class="ratio ratio-4x3"> <!-- Más pequeña que 1x1 -->
                <img src="<?= $song['album_art_url'] ?: 'https://via.placeholder.com/300x225?text=Album' ?>"
                    class="img-fluid w-100 h-100 object-fit-cover" alt="Album Art">
            </div>

            <!-- Overlay con acciones -->
            <div class="song-overlay d-flex justify-content-center align-items-center gap-2">
                <button class="btn btn-light btn-icon view-song" data-song-id="<?= $song['id'] ?>" title="View">
                    <i class="material-symbols-rounded">visibility</i>
                </button>
                <?php if ($is_admin || $is_worship_leader): ?>
                    <button class="btn btn-dark btn-icon edit-song" data-song-id="<?= $song['id'] ?>" title="Edit">
                        <i class="material-symbols-rounded">edit</i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info -->
        <div class="p-2">
            <h6 class="fw-semibold text-truncate mb-1" style="font-size: 14px;"><?= htmlspecialchars($song['title']) ?></h6>
            <p class="text-muted small mb-1 text-truncate"><?= htmlspecialchars($song['artist']) ?></p>
            <div class="d-flex justify-content-between text-muted" style="font-size: 12px;">
                <span><i class="material-symbols-rounded align-middle me-1" style="font-size:14px;">music_note</i><?= $song['key_signature'] ?: '-' ?></span>
                <span><i class="material-symbols-rounded align-middle me-1" style="font-size:14px;">speed</i><?= $song['bpm'] ?: '-' ?> BPM</span>
            </div>
        </div>
    </div>
</div>