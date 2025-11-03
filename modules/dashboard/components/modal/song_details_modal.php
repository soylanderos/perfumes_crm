<div class="modal fade" id="songDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow-lg border-0">

            <!-- HEADER -->
            <div class="modal-header bg-gradient-primary text-white d-flex align-items-center">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="material-symbols-rounded">music_note</i>
                    <?= htmlspecialchars($song_details['title']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Imagen -->
                    <div class="col-12 col-md-4 text-center">
                        <div class="ratio ratio-1x1 rounded-4 overflow-hidden border shadow-sm mx-auto" style="max-width: 250px;">
                            <img src="<?= $song_details['album_art_url'] ?: 'https://via.placeholder.com/400x400?text=Album' ?>"
                                alt="Album Art" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="col-12 col-md-8">
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($song_details['title']) ?></h4>
                        <p class="text-muted mb-3"><?= htmlspecialchars($song_details['artist']) ?></p>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="material-symbols-rounded align-middle me-1">music_note</i>
                                <?= $song_details['key_signature'] ?: 'N/A' ?>
                            </span>
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="material-symbols-rounded align-middle me-1">speed</i>
                                <?= $song_details['bpm'] ?: 'N/A' ?> BPM
                            </span>
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="material-symbols-rounded align-middle me-1">calendar_month</i>
                                <?= date('M d, Y', strtotime($song_details['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Lyrics -->
                <div class="mt-4">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="material-symbols-rounded align-middle me-1">lyrics</i> Lyrics
                    </h6>
                    <?php if (!empty($song_details['lyrics_file_url'])): ?>
                        <a href="<?= htmlspecialchars($song_details['lyrics_file_url']) ?>" target="_blank"
                            class="btn btn-outline-primary w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="material-symbols-rounded">description</i> View Lyrics File
                        </a>
                    <?php elseif (!empty($song_details['lyrics'])): ?>
                        <div class="p-3 bg-light rounded shadow-sm" style="max-height: 200px; overflow-y: auto; white-space: pre-wrap;">
                            <?= nl2br(htmlspecialchars($song_details['lyrics'])) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No lyrics available.</p>
                    <?php endif; ?>
                </div>

                <!-- Notes -->
                <div class="mt-3">
                    <h6 class="fw-bold text-secondary mb-2">
                        <i class="material-symbols-rounded align-middle me-1">sticky_note_2</i> Notes
                    </h6>
                    <?php if (!empty($song_details['notes_file_url'])): ?>
                        <a href="<?= htmlspecialchars($song_details['notes_file_url']) ?>" target="_blank"
                            class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="material-symbols-rounded">attach_file</i> View Notes File
                        </a>
                    <?php elseif (!empty($song_details['notes'])): ?>
                        <div class="p-3 bg-white border rounded shadow-sm" style="white-space: pre-wrap;">
                            <?= nl2br(htmlspecialchars($song_details['notes'])) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No notes provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer bg-light rounded-bottom-4">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <button class="btn btn-primary edit-song" data-song-id="<?= $song_details['id'] ?>">
                        <i class="material-symbols-rounded align-middle me-1">edit</i> Edit Song
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4a6cf7, #6a11cb);
    }

    .modal-content {
        border: none;
    }

    .btn-outline-primary:hover,
    .btn-outline-secondary:hover {
        opacity: 0.9;
    }
</style>