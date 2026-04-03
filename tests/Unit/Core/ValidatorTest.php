<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Tests\TestCase;
use Core\Validator;

/**
 * ValidatorTest
 * Tests the security and validation logic for file uploads in the CMS.
 */
class ValidatorTest extends TestCase
{
    /**
     * Test successful validation of a legitimate PNG image.
     */
    public function test_validate_file_success_with_valid_image()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_img');
        // Generate a real 1x1 PNG
        $img = imagecreatetruecolor(1, 1);
        imagepng($img, $tmpFile);
        imagedestroy($img);

        $file = [
            'name' => 'pillar_test.png',
            'type' => 'image/png',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile)
        ];

        $errors = Validator::validateFile($file, 2 * 1024 * 1024, ['png', 'jpg', 'webp']);
        
        @unlink($tmpFile);
        
        $this->assertEmpty($errors, "Validator should allow a real PNG image.");
    }

    /**
     * Test file size enforcement.
     */
    public function test_validate_file_fails_when_exceeding_max_size()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'large');
        file_put_contents($tmpFile, str_repeat('A', 1024)); // Any file content

        $file = [
            'name' => 'oversized.png',
            'type' => 'image/png',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 6 * 1024 * 1024 // Fake size to trigger size check
        ];

        // Max size set to 5MB
        $errors = Validator::validateFile($file, 5 * 1024 * 1024, ['png']);
        
        @unlink($tmpFile);
        
        $this->assertContains('El archivo excede el tamaño máximo permitido.', $errors);
    }

    /**
     * Test extension whitelist enforcement.
     */
    public function test_validate_file_fails_with_disallowed_extension()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'mal');
        file_put_contents($tmpFile, '<?php phpinfo(); ?>');

        $file = [
            'name' => 'malicious.php',
            'type' => 'application/x-php',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 1024
        ];

        $errors = Validator::validateFile($file, 5 * 1024 * 1024, ['png', 'jpg']);
        
        @unlink($tmpFile);
        
        $this->assertContains("Extensión .php no permitida.", $errors);
    }

    /**
     * Test protection against type spoofing (Renaming .exe to .jpg).
     */
    public function test_validate_file_detects_mime_type_spoofing()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'spoof');
        file_put_contents($tmpFile, "<?php echo 'Hacked'; ?>"); // PHP content in .jpg

        $file = [
            'name' => 'shell.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile)
        ];

        $errors = Validator::validateFile($file, 5 * 1024 * 1024, ['jpg', 'jpeg']);
        
        @unlink($tmpFile);
        
        $this->assertContains("Contenido del archivo no coincide con su extensión (Falsificación de tipo).", $errors);
    }

    /**
     * Test SVG support (Critical for "Pillars" UI).
     */
    public function test_validate_file_allows_svg()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_svg');
        file_put_contents($tmpFile, '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>');

        $file = [
            'name' => 'icon.svg',
            'type' => 'image/svg+xml',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile)
        ];

        $errors = Validator::validateFile($file, 5 * 1024 * 1024, ['svg']);
        
        @unlink($tmpFile);
        
        $this->assertEmpty($errors, "Validator should allow valid SVG files.");
    }
}
