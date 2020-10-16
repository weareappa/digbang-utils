<?php

namespace Digbang\Utils\Enumerables;

abstract class State extends Enum
{
    protected const TRANSITIONS = [];
    protected const INITIAL = [];

    /** @var array */
    protected $log = [];

    /** @var string */
    protected $value;

    public function __construct($value = null)
    {
        if (! in_array($value, static::INITIAL, true)) {
            throw new \InvalidArgumentException('State can\'t initiate from ' . $value);
        }

        parent::__construct($value);

        $this->log[] = [
            'date' => (new \DateTime())->getTimestamp(),
            'from' => null,
            'to' => $this->getValue(),
            'notes' => null,
        ];
    }

    public function transition(string $to, $notes = null): self
    {
        self::assert($to);

        if (! $this->canTransition($to)) {
            throw new \InvalidArgumentException('State can\'t transition from ' . $this->getValue() . ' to ' . $to);
        }

        $transition = clone $this;
        $transition->value = $to;

        $transition->log[] = [
            'date' => (new \DateTime())->getTimestamp(),
            'from' => $this->getValue(),
            'to' => $transition->getValue(),
            'notes' => $notes,
        ];

        return $transition;
    }

    public function canTransition(string $to): bool
    {
        if ($this->getPossibleTransitions() === null) {
            return true;
        }

        return in_array($to, $this->getPossibleTransitions(), true);
    }

    /**
     * Returns null if transition is not specified.
     */
    public function getPossibleTransitions(): ?array
    {
        return static::TRANSITIONS[$this->getValue()] ?? null;
    }

    /**
     * Returns null if transition is not specified.
     */
    public function getPossibleTransitionsFrom(string $value): ?array
    {
        return static::TRANSITIONS[$value] ?? null;
    }

    public function getPossibleInitial(): array
    {
        return static::INITIAL;
    }

    public function getLog(): array
    {
        return $this->log;
    }

    public function hasBeenAtState(string $state): bool
    {
        $states = array_map(function (array $item) {
            return $item['to'];
        }, $this->log);

        return in_array($state, $states, true);
    }

    public static function getAllValues(): array
    {
        $oClass = new \ReflectionClass(get_called_class());

        return array_values(array_filter(
            $oClass->getConstants(),
            function ($constant): bool {
                return $constant !== 'INITIAL' && $constant !== 'TRANSITIONS';
            },
            ARRAY_FILTER_USE_KEY
        ));
    }

    public static function getAllValuesTranslated(): array
    {
        $oClass = get_called_class();
        $values = static::getAllValues();

        return array_combine(
            $values,
            array_map(
                function (string $value) use ($oClass) {
                    return (string) trans("enum.$oClass.$value");
                },
                $values
            )
        );
    }
}
