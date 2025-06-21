<?php
// Wir erwarten jetzt $slot (Name & Daten des Slots) und $mediaLookup (alle Medien)
// In der Template-Datei wird diese View so aufgerufen:
// echo $this->include('...', ['slot' => ['name' => 'col-1', 'data' => $slots['col-1'] ?? null]]);
// Die Variable $mediaLookup wird vom Eltern-Layout `edit.php` automatisch durchgereicht.

$slotName = $slot['name'];
$slotData = $slot['data'];
?>
<div class="layout-slot border rounded d-flex justify-content-center align-items-center bg-light text-center p-2" style="min-height: 200px; height: 100%;">

  <?php if ($slotData && $slotData->media_uuid): ?>

    <?php // --- SLOT IST BEFÜLLT --- 
    ?>
    <?php $media = $mediaLookup[$slotData->media_uuid] ?? null; ?>

    <?php if ($media): ?>
      <div>
        <div class="media-thumbnail mx-auto mb-2">
          <?php if (str_starts_with($media->file_type, 'image/')): ?>
            <img src="<?= site_url('media/serve/' . $media->uuid) ?>" alt="<?= esc($media->original_name) ?>" class="img-fluid">
          <?php elseif ($media->file_type === 'application/pdf'): ?>
            <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 2.5rem;"></i>
          <?php else: ?>
            <i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>
          <?php endif; ?>
        </div>
        <p class="mb-1 small text-muted" style="word-break: break-all; line-height: 1.2;">
          <?= esc($media->original_name) ?>
        </p>
        <button class="btn btn-sm btn-secondary js-assign-content mt-1" data-slot-name="<?= esc($slotName) ?>">Ändern</button>
      </div>
    <?php endif; ?>

  <?php else: ?>

    <?php // --- SLOT IST LEER --- 
    ?>
    <div>
      <p><strong><?= esc($slotName) ?></strong></p>
      <p><i class="bi bi-plus-circle-dotted" style="font-size: 2.5rem;"></i></p>
      <button class="btn btn-sm btn-primary js-assign-content" data-slot-name="<?= esc($slotName) ?>">Inhalt zuweisen</button>
    </div>

  <?php endif; ?>

</div>