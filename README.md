# zenstruck/dimension

[![CI Status](https://github.com/zenstruck/dimension/workflows/CI/badge.svg)](https://github.com/zenstruck/dimension/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/zenstruck/dimension/branch/1.x/graph/badge.svg?token=255O1QA2UU)](https://codecov.io/gh/zenstruck/dimension)

Wrap quantity and unit of measure with conversions/humanizers.

## Installation

```bash
composer require zenstruck/dimension
```

## Usage

A _dimension_ consists of a _quantity_ (`numeric`) and a _unit of measure_ (`string`).

### Dimension Object

```php
use Zenstruck\Dimension;

// create
$dimension = new Dimension(45.458, 'ft');
$dimension = Dimension::from('45.458ft'); // equivalent to above

$dimension->quantity(); // 45.458
$dimension->unit(); // "ft"

// render
$dimension->toString(); // "45.46 ft" (max 2 decimal places)
(string) $dimension; // equivalent to above

$dimension->toArray(); // [45.458, "ft"]
json_encode($dimension); // '[45.458, "ft"]'

// use your own formatter
vsprintf('%.4f%s', $dimension->toArray()); // "45.4580ft"
```

#### Conversions

A dimension object can be converted to alternate units. The following converters are available:

* _Mass_
* _Length_
* _Temperature_
* _Duration_ (length of time)
* _Information_ (bytes)
* _Propose additional converters via issue/PR_

Use the `convertTo()` method to perform conversions:

```php
use Zenstruck\Dimension;

$dimension = Dimension::from('45ft');

$converted = $dimension->convertTo('m'); // Zenstruck\Dimension
$converted->quantity(); // 13.716
$converted->unit(); // "m"
$converted->toString(); // "13.71 m"

$dimension->convertTo('g'); // throws ConversionNotPossible - cannot convert feet to grams
```

#### Comparisons

Several comparison methods are available:

```php
use Zenstruck\Dimension;

$dimension = Dimension::from('45ft');

$dimension->isEqualTo('6m'); // false
$dimension->isLargerThan('6m'); // true
$dimension->isLargerThanOrEqualTo('6m'); // true
$dimension->isSmallerThan('6m'); // false
$dimension->isSmallerThanOrEqualTo('6m'); // false
$dimension->isWithin('6m', '1km'); // true
$dimension->isOutside('6m', '1km'); // false
```

#### Mathematical Operations

```php
use Zenstruck\Dimension;

$dimension = Dimension::from('45ft');

$dimension->add('6m')->toString(); // "64.69 ft"
$dimension->subtract('6m')->toString(); // "25.31 ft"
```

### Information Object

`Zenstruck\Dimension\Information` extends `Zenstruck\Dimension` so it has the [same API](#dimension-object) with
some additional features related to humanizing bytes.

```php
use Zenstruck\Dimension\Information;

$info = Information::from('4568897B');
$info = Information::from(4568897); // equivalent to above (can create from bytes directly)

$info->bytes(); // 4568897

// "humanize"
(string) $info->humanize(); // "4.57 MB" (defaults to the decimal system)
(string) $info->asBinary()->humanize(); // "4.36 MiB" (convert to binary system before humanizing)

(string) Information::binary(4568897)->humanize(); // "4.36 MiB" (explicitly create in binary system)

// when creating with a unit of measure, the system is detected
(string) Information::from('4570 kb')->humanize(); // "4.57 MB"
(string) Information::from('4570 KiB')->humanize(); // "4.46 MiB"
```

### Duration Object

`Zenstruck\Dimension\Duration` extends `Zenstruck\Dimension` so it has the [same API](#dimension-object) with
the ability to _humanize_ a duration.

```php
use Zenstruck\Dimension\Duration;

$duration = Duration::from('8540 s');
$duration = Duration::from(8540); // equivalent to above (can create from seconds directly)

(string) $duration->humanize(); // "2 hrs"

(string) Duration::from(0)->humanize(); // "0 secs"
(string) Duration::from(1)->humanize(); // "1 sec"
(string) Duration::from(10)->humanize(); // "10 secs"
(string) Duration::from(65)->humanize(); // "1 min"
```

## Bridge

### Twig Extension

A [Twig](https://twig.symfony.com) extension is provided with `dimension`, `information`, and `duration` filters.

Manual Activation:

```php
/* @var Twig\Environment $twig */

$twig->addExtension(new \Zenstruck\Dimension\Bridge\Twig\DimensionExtension());
```

Symfony full-stack activation:

```yaml
# config/packages/zenstruck_dimension.yaml

Zenstruck\Dimension\Bridge\Twig\DimensionExtension: ~

# If not using auto-configuration:
Zenstruck\Dimension\Bridge\Twig\DimensionExtension:
    tag: twig.extension
```

Usage:

```twig
{{ '45.458ft'|dimension.convertTo('m') }} {# "13.71 m" #}

{{ 4568897|information.humanize() }} {# "4.57 MB" #}

{{ 8540|duration.humanize() }} {# "2 hrs" #}
```
