<?php
declare(strict_types=1);

namespace Core\Components\Image;

// Image Manipulation Class
class Image implements ImageInterface
{
    use ImageTrait;

    public function __construct(string $imagePath = null)
    {
        if ($imagePath !== null) {
            $this->setImageSource($imagePath);
        }
    }
}
