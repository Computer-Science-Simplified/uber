<?php

namespace App\Services;

use App\Enums\RideStatus;
use App\Models\Ride;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;

class RideStateMachine
{
    private StateMachine $stateMachine;

    public function __construct(Ride $ride)
    {
        $this->stateMachine = new StateMachine();

        $this->stateMachine->addState(new State(RideStatus::Waiting->value, StateInterface::TYPE_INITIAL));
        $this->stateMachine->addState(new State(RideStatus::Accepted->value, StateInterface::TYPE_NORMAL));
        $this->stateMachine->addState(new State(RideStatus::InProgress->value, StateInterface::TYPE_NORMAL));
        $this->stateMachine->addState(new State(RideStatus::Abandoned->value, StateInterface::TYPE_NORMAL));
        $this->stateMachine->addState(new State(RideStatus::Finished->value, StateInterface::TYPE_FINAL));

        $this->stateMachine->addTransition('accept', RideStatus::Waiting->value, RideStatus::Accepted->value);
        $this->stateMachine->addTransition('progress', RideStatus::Accepted->value, RideStatus::InProgress->value);
        $this->stateMachine->addTransition('finish', RideStatus::InProgress->value, RideStatus::Finished->value);

        $this->stateMachine->addTransition('abandon-from-waiting', RideStatus::Waiting->value, RideStatus::Abandoned->value);
        $this->stateMachine->addTransition('abandon-from-accepted', RideStatus::Accepted->value, RideStatus::Abandoned->value);

        $this->stateMachine->setObject($ride);

        $this->stateMachine->initialize();
    }

    public function can(string $transition): bool
    {
        return $this->stateMachine->can($transition);
    }
}
