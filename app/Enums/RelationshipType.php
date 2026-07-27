<?php

namespace App\Enums;

enum RelationshipType: string
{
    // Social
    case Knows = 'knows';
    case RelatedTo = 'related_to';
    case AlliedWith = 'allied_with';
    case Opposes = 'opposes';

    // Organisational
    case MemberOf = 'member_of';
    case Leads = 'leads';
    case EmployedBy = 'employed_by';

    // Spatial
    case LocatedIn = 'located_in';
    case Borders = 'borders';
    case OriginatesFrom = 'originates_from';

    // Possession and authority
    case Owns = 'owns';
    case Controls = 'controls';
    case Governs = 'governs';

    // Conceptual
    case BelievesIn = 'believes_in';
    case Created = 'created';
    case DependsOn = 'depends_on';
}
