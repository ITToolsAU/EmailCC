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
        $this->email = (string) $email;
    }

    public function get(): array
    {
        return array_filter(explode(',', $this->email));
    }

}
