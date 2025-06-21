<?php if (! empty($media) && is_array($media)):
  foreach ($media as $item): ?>
    <div class="list-group-item d-flex justify-content-between align-items-center js-media-item"
      style="cursor: pointer;"
      data-media-uuid="<?= $item->uuid ?>">

      <div class="d-flex align-items-center">
        <div class="media-thumbnail me-3">
          <?php if (str_starts_with($item->file_type, 'image/')): ?>
            <img src="<?= site_url('media/serve/' . $item->uuid) ?>" alt="<?= esc($item->original_name) ?>" class="img-fluid">
          <?php elseif ($item->file_type === 'application/pdf'): ?>
            <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 2.5rem;"></i>
          <?php else: ?>
            <i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>
          <?php endif; ?>
        </div>
        <div>
          <strong class="media-filename" data-bs-toggle="tooltip" title="<?= esc($item->original_name) ?>">
            <?= esc($item->original_name) ?>
          </strong>
          <small class="text-muted d-block">
            Größe: <?= esc($item->getReadableSize()) ?> | Typ: <?= esc($item->file_type) ?>
          </small>
        </div>
      </div>

      <div>
        <?php if (isset($context) && $context === 'modal'): ?>
          <button type="button" class="btn btn-primary btn-sm">
            <i class="bi bi-check-circle-fill me-1"></i> Auswählen
          </button>
        <?php else: ?>
          <div class="btn-group" role="group" aria-label="Aktionen">

            <button type="button" class="btn btn-outline-secondary btn-sm js-preview-media"
              data-bs-toggle="modal"
              data-bs-target="#previewModal"
              data-uuid="<?= $item->uuid ?>"
              data-mime-type="<?= $item->file_type ?>"
              title="Vorschau anzeigen">
              <i class="bi-eye-fill"></i>
            </button>

            <button type="button" class="btn btn-outline-danger btn-sm js-delete-media"
              data-delete-url="<?= site_url('media/delete/' . $item->uuid) ?>"
              data-confirm-message="Sind Sie sicher, dass Sie die Datei '<?= addslashes(esc($item->original_name)) ?>' löschen möchten?"
              title="Medium löschen">
              <i class="bi-trash-fill"></i>
            </button>

          </div>
        <?php endif; ?>
      </div>
    </div>
<?php endforeach;
endif;
