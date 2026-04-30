<?php
declare(strict_types=1);

/**
 * UploadService — handles file upload validation and storage.
 */
class UploadService
{
    /** Process a file upload from $_FILES.
     *
     * @param array $file  Entry from $_FILES (e.g. $_FILES['document'])
     * @return array{stored_filename:string, file_path:string, original_filename:string, mime_type:string, size_bytes:int}
     * @throws RuntimeException on any validation failure
     */
    public static function process(array $file): array
    {
        // Basic PHP upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::uploadErrorMessage($file['error']));
        }

        $size = (int)$file['size'];
        if ($size === 0) {
            throw new RuntimeException('Tiedosto on tyhjä.');
        }
        if ($size > MAX_UPLOAD_BYTES) {
            throw new RuntimeException(
                'Tiedosto on liian suuri. Maksimikoko on ' . format_bytes(MAX_UPLOAD_BYTES) . '.'
            );
        }

        // Validate extension
        $originalName = basename($file['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException(
                'Tiedostomuoto ei ole sallittu. Sallitut muodot: ' . implode(', ', ALLOWED_EXTENSIONS)
            );
        }

        // Validate MIME type using finfo (don't trust client-provided MIME)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
            throw new RuntimeException(
                'Tiedoston sisältötyyppi ei ole sallittu. (' . htmlspecialchars($mime) . ')'
            );
        }

        // Generate unique filename
        $storedName = self::generateFilename($ext);
        $destPath   = UPLOADS_PATH . '/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException('Tiedoston tallennus epäonnistui.');
        }

        // Set restrictive permissions
        chmod($destPath, 0640);

        return [
            'stored_filename'   => $storedName,
            'file_path'         => $destPath,
            'original_filename' => $originalName,
            'mime_type'         => $mime,
            'size_bytes'        => $size,
        ];
    }

    /** Delete a stored file by filename (not path). */
    public static function delete(string $storedFilename): void
    {
        $path = UPLOADS_PATH . '/' . basename($storedFilename);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private static function generateFilename(string $ext): string
    {
        return date('Ymd') . '_' . bin2hex(random_bytes(12)) . '.' . $ext;
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Tiedosto on liian suuri.',
            UPLOAD_ERR_PARTIAL  => 'Tiedoston lataus epäonnistui (osittainen).',
            UPLOAD_ERR_NO_FILE  => 'Tiedostoa ei valittu.',
            UPLOAD_ERR_NO_TMP_DIR => 'Väliaikaishakemisto puuttuu.',
            UPLOAD_ERR_CANT_WRITE => 'Tiedoston kirjoitus epäonnistui.',
            default             => 'Tuntematon tiedostovirhe.',
        };
    }
}
