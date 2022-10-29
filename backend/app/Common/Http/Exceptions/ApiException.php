<?php

declare(strict_types=1);

namespace App\Common\Http\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Pure;
use Throwable;

class ApiException extends Exception
{
    /** @var array<string, mixed> */
    protected ?array $errors = null;

    /**
     * @param array<string, mixed>|null $errors
     */
    #[Pure]
    public function __construct(?array $errors = null, string $message = '', int $code = 422, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }
    /**
     * Render the exception into an HTTP response.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        $errors = null;

        if (is_array($this->errors)) {
            foreach ($this->errors as $field => $error) {
                $errors[$field] = is_array($error) ?: [$error];
            }
        }

        return response()->json(
            [
                'message' => $this->message ?: null,
                'errors' => $errors,
            ],
            $this->code
        );
    }
}
