<?php

namespace Reliese\Meta;

use Reliese\Meta\Column;

interface ColumnParser
{
    public function normalize(): Column;
}
