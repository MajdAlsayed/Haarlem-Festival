<?php
/** @var array<int, array<string, mixed>> $tours */
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['tours' => $tours ?? []], JSON_THROW_ON_ERROR);
