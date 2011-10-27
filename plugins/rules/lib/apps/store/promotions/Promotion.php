<?php
namespace apps\store\promotions;

/**
 * A promotion.
 */
class Promotion {
    private $name;
    private $startDate;
    private $endDate;
    // map: promotion element class (optionally bean) => config
    private $qualifications;
    // map: promotion element class (optionally bean) => config
    private $conditions;
    // list of actions if promotion applies
    private $actions;
}
