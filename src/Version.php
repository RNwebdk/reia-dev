<?php
namespace ReiaDev;

class Version {
    private int $major;
    private int $minor;
    private int $patch;

    public function __construct(int $major, int $minor, int $patch) {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
    }
    public function getMajor(): int {
        return $this->major;
    }
    public function getMinor(): int {
        return $this->minor;
    }
    public function getPatch(): int {
        return $this->patch;
    }
    public function getVersion(): string {
        return $this->getMajor() . "." . $this->getMinor() . "." . $this->getPatch();
    }
}
