<?php
namespace Digbang\Utils;

/**
 * Constant definition example:
 * const A = 1 << 0;
 * const B = 1 << 1;
 * const C = 1 << 2;
 * const D = 1 << 3;
 */
abstract class EnumFlag extends EnumBase
{
    /** @var int */
    protected $value;

    public function __construct(int $flags)
    {
        static::assertFlagsAreValid();
        static::assert($flags);

        $this->value = $flags;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * @return EnumFlag[]
     */
    public static function getAllAsObjects(): array
    {
        return array_map(function ($value) {
            return new static($value);
        },
            array_filter(static::getAllValues()));
    }


    /**
     * @throws \InvalidArgumentException
     */
    public static function from(int $flags): EnumFlag
    {
        return new static ($flags);
    }

    protected static function assert(int $flags)
    {
        if ($flags <= 0 || $flags >= 2 ** static::count()) {
            throw new \InvalidArgumentException('Provided $flags is not valid.');
        }
    }

    private static function assertFlagsAreValid()
    {
        $oClass = new \ReflectionClass(get_called_class());
        $flags = $oClass->getConstants();
        sort($flags);
        foreach ($flags as $position => $flag) {
            if ($flag !== 2 ** $position) {
                throw new \LogicException('Defined constants in ' . $oClass->getName() . ' are not valid.');
            }
        }
    }

    /**
     * Returns current object values as string of the constant name.
     * @return string[]
     */
    public function getNamedValues(): array
    {
        $current = [];
        foreach (static::getAllInversed() as $value => $name) {
            if ($this->has($value)) {
                $current[] = $name;
            }
        }

        return $current;
    }


    /**
     * Returns a new EnumFlag with provided flags added.
     */
    public function add(int $flags): EnumFlag
    {
        if (($this->value | $flags) >= (2 ** static::count())) {
            throw new \LogicException('Resulting value is not valid.');
        }

        $new = new static(0);
        $new->value = $this->value | $flags;

        return $new;
    }


    /**
     * Returns a new EnumFlag with provided flags removed.
     */
    public function remove(int $flags): EnumFlag
    {
        $new = new static(0);
        $new->value = $this->value & ~$flags;

        return $new;
    }

    public function has(int $flags): bool
    {
        if ($flags === $this->value) {
            return true;
        }

        return (bool)($this->value & $flags);
    }

    public function hasNot(int $flags): bool
    {
        return !$this->has($flags);
    }
}