<?php

namespace Digbang\Utils\Tests;

use Digbang\Utils\Enumerables\State;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    public function test_change_status_valid_transition()
    {
        $state = new StateImplementation(StateImplementation::STEP_1);
        $this->assertEquals(StateImplementation::STEP_1, $state->getValue());
        $newState = $state->transition(StateImplementation::STEP_2);
        $this->assertEquals(StateImplementation::STEP_2, $newState->getValue());
    }

    public function test_change_log()
    {
        $state = new StateImplementation(StateImplementation::STEP_1);
        $this->assertEquals(StateImplementation::STEP_1, $state->getValue());
        $newState = $state->transition(StateImplementation::STEP_2, ['key' => 'value']);
        $newState = $newState->transition(StateImplementation::STEP_3, ['key2' => 'value2']);
        $this->assertNotEmpty($newState->getLog());
    }

    public function test_change_status_invalid_transition()
    {
        $this->expectException(InvalidArgumentException::class);

        $state = new StateImplementation(StateImplementation::STEP_1);
        $state->transition(StateImplementation::STEP_4);
    }

    public function test_change_status_undeclared_transition()
    {
        $state = new StateImplementation(StateImplementation::STEP_1);
        $state = $state->transition(StateImplementation::STEP_2);
        $state->transition(StateImplementation::STEP_4);
        $this->assertEquals($state->getValue(), StateImplementation::STEP_2);
    }

    public function test_get_possible_states_transitions_for()
    {
        $state = new StateImplementation(StateImplementation::STEP_1);
        $this->assertEquals([
            StateImplementation::STEP_2,
            StateImplementation::STEP_3,
        ], $state->getPossibleTransitions());
    }

    public function test_exception_initial_state()
    {
        $this->expectException(InvalidArgumentException::class);

        new StateImplementation(StateImplementation::STEP_4);
    }

    public function test_empty_possible_transition_state()
    {
        $state = new StateImplementation(StateImplementation::STEP_1);
        $newState = $state->transition(StateImplementation::STEP_2);
        $this->assertEmpty($newState->getPossibleTransitions());
    }
}

class StateImplementation extends State
{
    const STEP_1 = 'Paso1';
    const STEP_2 = 'Paso2';
    const STEP_3 = 'Paso3';
    const STEP_4 = 'Paso4';

    const TRANSITIONS = [
        StateImplementation::STEP_1 => [
            StateImplementation::STEP_2,
            StateImplementation::STEP_3,
        ],
    ];

    const INITIAL = [
        StateImplementation::STEP_1,
    ];
}
