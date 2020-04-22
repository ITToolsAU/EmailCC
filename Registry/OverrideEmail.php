<?php declare(strict_types=1);

namespace Xigen\CC\Registry;

class OverrideEmail
{
    /**
     * @var ProductInterface
     */
    private $email = '';

    public function set(string $email): void
    {
        $this->email = $email;
    }

    public function get(): array
    {
        return explode(',', $this->email);
    }

}