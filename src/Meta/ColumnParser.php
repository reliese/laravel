<?php

namespace Reliese\Meta;

interface ColumnParser
{
    public function normalize(): Column;
}
