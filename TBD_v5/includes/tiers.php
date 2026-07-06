<?php
  /* ------------------------------------------------------------------ *
   *  SPONSORSHIP PACKAGES — single source of truth.
   *  Consumed by sponsor.php (full cards) and become-a-sponsor.php (summary).
   *    'price' : dollar amount, or any label (e.g. 'In-kind donation')
   *    'kind'  : 'pay'    -> routes to the Blackbaud checkout (become-a-sponsor.php)
   *              'inkind' -> routes to the in-kind contact form (sponsor-in-kind.php)
   *    'state' : 'open'   -> orange availability pill
   *              'closed' -> teal pill (e.g. "Goal achieved")
   *    'desc'  : array of intro paragraph(s) shown under the title
   *    'note'  : optional small line shown just above the benefits list
   *    'perks' : the benefits, shown as a checked list
   * ------------------------------------------------------------------ */
  $tiers = [
    [
      'n'=>1, 'name'=>'Presenting Sponsor', 'price'=>'$5,000', 'kind'=>'pay',
      'avail'=>'1 spot available', 'state'=>'open',
      'desc'=>['Our premier sponsorship level, offering the highest visibility throughout the tournament and a strong connection to the mission behind the event.'],
      'perks'=>[
        'Premier logo placement on the tournament website, the main hub for event promotion and registration',
        'Logo recognition on event bracket and schedule materials',
        'Logo on player arm bands worn throughout the tournament',
        'Dedicated pre-event social media post introducing your business or organization as the Presenting Sponsor',
        'Table or tent space at the event for sponsor presence, giveaways, or guest engagement',
        'Event-day verbal recognition and appreciation',
      ],
    ],
    [
      'n'=>2, 'name'=>'Court Sponsor', 'price'=>'$2,000', 'kind'=>'pay',
      'avail'=>'2 spots available', 'state'=>'open',
      'desc'=>['Put your brand directly in the action with high-visibility court signage and event-day recognition.'],
      'perks'=>[
        'Highly visible logo placement on a banner displayed on one tournament court',
        'Option to name the court, such as "Court 1 presented by [Company Name]"',
        'Logo recognition on event bracket and schedule materials',
        'Logo placement on the tournament website',
        'Tagged pre-event social media recognition',
        'Event-day verbal recognition and appreciation',
      ],
    ],
    [
      'n'=>3, 'name'=>'Team Sponsor', 'price'=>'$500', 'kind'=>'pay',
      'avail'=>'12 spots available', 'state'=>'open',
      'desc'=>['Support one of the tournament teams and be part of their day on the sand. This is a great option for small businesses, families, groups, or friends who want to support the mission in a fun and visible way.'],
      'perks'=>[
        'Logo on one team\'s arm bands, worn by four players throughout the tournament',
        'Logo recognition on the event bracket',
        'Social media shoutout or tag recognizing your sponsorship',
        'Team assignment and scheduled play time shared with you before the event, so you can come cheer on "your" team',
        'Event-day verbal recognition and appreciation',
      ],
    ],
    [
      'n'=>4, 'name'=>'Match Sponsor', 'price'=>'$125', 'kind'=>'pay',
      'avail'=>'Spots available', 'state'=>'open',
      'desc'=>[
        'A mission-focused sponsorship that helps make the tournament experience possible for a Big and their Little.',
        'Your sponsorship helps cover a Big Brothers Big Sisters match — one Big and one Little — to play in the tournament together and enjoy lunch at the event.',
      ],
      'perks'=>[
        'Recognition as a Match Sponsor',
        'A meaningful connection to the mentoring mission behind The Big Draw',
        'A photo from the day when possible, so you can see the match experience your sponsorship helped create',
        'Opportunity for organic event-day social media recognition',
      ],
    ],
    [
      'n'=>5, 'name'=>'In-Kind Sponsor', 'price'=>'In-kind donation', 'kind'=>'inkind',
      'avail'=>'Spots available', 'state'=>'open',
      'desc'=>['Support the event by donating items that improve the player experience or help us celebrate the winning teams.'],
      'note'=>'In-kind opportunities include:',
      'perks'=>[
        'Swag items for player bags',
        'Prizes for top-finishing teams',
        'Food, drinks, services, or event-day supplies',
        'Logo recognition on swag bag materials or prize table signage when applicable',
        'Event-day verbal recognition for in-kind donations valued at $500 or more',
      ],
    ],
  ];
