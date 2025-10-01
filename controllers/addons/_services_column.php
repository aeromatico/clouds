<?php
$services = $record->services;
if ($services && $services->count() > 0) {
    echo implode(', ', $services->pluck('name')->toArray());
} else {
    echo '<span class="text-muted">-</span>';
}
