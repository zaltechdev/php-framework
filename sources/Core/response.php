<?php

function view(string $name, array $data = [], int $code = HTTP_OK):array{
	return [
		"view" => [
			"data" => $data,
			"name" => $name,
			"code" => $code
		]
	];
}

function redirect(string $path):array{
	return ["redirect" => $path];
}