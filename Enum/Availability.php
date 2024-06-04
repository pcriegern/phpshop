<?php

namespace deepcommerce\phpshop\Enum;

/**
 * Class ValueType
 */
class Availability {
    const ANNOUNCED  = 'announced';
    const IN_STOCK   = 'in_stock';
    const AVAILABLE  = 'available';
    const SOLD_OUT   = 'soldout';
    const REORDERED  = 'reordered';
    const DELISTED   = 'delisted';
}
