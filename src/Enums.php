<?php

namespace Teknyo\NICValidator;

enum NICFormat: string
{
    case OLD = 'old';
    case NEW = 'new';
    case INVALID = 'invalid';
}

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
}