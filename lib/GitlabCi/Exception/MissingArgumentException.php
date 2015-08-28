<?php

namespace GitlabCi\Exception;

/**
 * MissingArgumentException
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class MissingArgumentException extends ErrorException
{
	public function __construct($required, $code = 0, $previous = NULL)
	{
		if (is_string($required)) {
			$required = [$required];
		}

		parent::__construct(sprintf('One or more of required ("%s") parameters is missing!', implode('", "', $required)), $code, $previous);
	}
}
