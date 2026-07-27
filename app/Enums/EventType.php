<?php

namespace App\Enums;

enum EventType: string
{
    case Birth = 'birth';
    case Death = 'death';
    case Meeting = 'meeting';
    case Journey = 'journey';
    case Conflict = 'conflict';
    case Conquest = 'conquest';
    case Discovery = 'discovery';
    case Creation = 'creation';
    case Destruction = 'destruction';
    case PoliticalChange = 'political_change';
    case NaturalEvent = 'natural_event';
}
