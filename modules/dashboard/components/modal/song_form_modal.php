<div class="modal fade" id="songFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow-lg border-0">

            <!-- HEADER -->
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="material-symbols-rounded">library_music</i>
                    <span id="modalTitle"><?= $title_form ?></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <form id="songForm" enctype="multipart/form-data">
                    <input type="hidden" name="song_id" id="song_id">

                    <!-- Title & Artist -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Song Title</label>
                            <input value="<?= $song_title ?>" type="text" name="title" id="song_title" class="form-control" placeholder="Enter song title" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Artist</label>
                            <input value="<?= $song_artist ?>" type="text" name="artist" id="song_artist" class="form-control" placeholder="Enter artist name">
                        </div>
                    </div>

                    <!-- Key & BPM -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Key Signature</label>
                            <select name="key_signature" id="key_signature" class="form-select">
                                <option selected disabled>Select Key</option>
                                <option <?= $key_signature == 'C' ? 'selected' : '' ?>>C</option>
                                <option <?= $key_signature == 'Db' ? 'selected' : '' ?>>Db</option>
                                <option <?= $key_signature == 'D' ? 'selected' : '' ?>>D</option>
                                <option <?= $key_signature == 'Eb' ? 'selected' : '' ?>>Eb</option>
                                <option <?= $key_signature == 'E' ? 'selected' : '' ?>>E</option>
                                <option <?= $key_signature == 'F' ? 'selected' : '' ?>>F</option>
                                <option <?= $key_signature == 'Gb' ? 'selected' : '' ?>>Gb</option>
                                <option <?= $key_signature == 'G' ? 'selected' : '' ?>>G</option>
                                <option <?= $key_signature == 'Ab' ? 'selected' : '' ?>>Ab</option>
                                <option <?= $key_signature == 'A' ? 'selected' : '' ?>>A</option>
                                <option <?= $key_signature == 'Bb' ? 'selected' : '' ?>>Bb</option>
                                <option <?= $key_signature == 'B' ? 'selected' : '' ?>>B</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">BPM</label>
                            <input value="<?= $bpm ?>" type="number" name="bpm" id="bpm" class="form-control" placeholder="Enter BPM">
                        </div>
                    </div>

                    <!-- Album Art -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Album Art</label>
                        <div class="d-flex gap-3 align-items-start flex-wrap">
                            <!-- Selector -->
                            <select class="form-select w-auto" id="album_mode">
                                <option value="link">Link</option>
                                <option value="file">File</option>
                            </select>
                            <!-- Input dinámico -->
                            <div id="album_input" class="flex-grow-1">
                                <input value="<?= $album_art_url ?>" type="text" name="album_art_url" id="album_art_url" class="form-control" placeholder="Enter image link">
                            </div>
                            <!-- Preview -->
                            <div id="album_preview" class="rounded border" style="width:80px; height:80px; background:#f8f9fa; display:flex; align-items:center; justify-content:center;">
                                <i class="material-symbols-rounded text-muted">image</i>
                            </div>
                        </div>
                    </div>

                    <!-- Lyrics -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lyrics</label>
                        <select class="form-select w-auto mb-2" id="lyrics_mode">
                            <option value="text">Text</option>
                            <option value="link">Link</option>
                            <option value="file">File</option>
                        </select>
                        <div id="lyrics_input">
                            <textarea name="lyrics" id="lyrics" class="form-control" rows="4" placeholder="Enter lyrics"><?= $lyrics ?></textarea>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <select class="form-select w-auto mb-2" id="notes_mode">
                            <option value="text">Text</option>
                            <option value="link">Link</option>
                            <option value="file">File</option>
                        </select>
                        <div id="notes_input">
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter notes"><?= $notes ?></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer bg-light rounded-bottom-4">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn_save_song" type="button" data-song-id="<?= $song_id ?>">
                    <i class="material-symbols-rounded align-middle me-1">save</i> Save Song
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    

    #album_preview img {
        object-fit: cover;
        border-radius: 8px;
    }

    .modal-body label {
        font-size: 14px;
    }

    select.form-select.w-auto {
        min-width: 110px;
    }
</style>