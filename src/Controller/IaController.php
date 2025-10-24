<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IaController extends AbstractController
{
    //No puedo poner la clave aqui porque OpenRouter la detecta y bloquea la bloquea
    //Puedes encontarla en el readme fragmentada
    //Se copia toda junta y ya funciona
    private const API_KEY = '';

    private function generateText(array $messages): string
    {
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        $data = [
            'model' => 'cognitivecomputations/dolphin-mistral-24b-venice-edition:free',
            'messages' => $messages,
        ];

        $payload = json_encode($data);

        $headers = [
            'Authorization: Bearer ' . self::API_KEY,
            'Content-Type: application/json',
            'HTTP-Referer: https://localhost',
            'X-Title: MiAppPHP',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Error en cURL: ' . curl_error($ch));
        }
        curl_close($ch);

        $jsonResponse = json_decode($response, true);
        if (!isset($jsonResponse['choices'])) {
            throw new \Exception('Respuesta inesperada: ' . print_r($jsonResponse, true));
        }

        return $jsonResponse['choices'][0]['message']['content'];
    }

    #[Route('/ia', name: 'ia')]
    public function ia(Request $request): Response
    {
        // Empezamos con el mensaje del sistema para definir el comportamiento del bot
        $messages = [
            ['role' => 'system', 'content' => 'Eres un asistente que habla español de España.'],
        ];

        $responseText = null;
        $userMessage = $request->request->get('prompt');

        if ($userMessage) {
            // Añadimos el mensaje del usuario
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            try {
                // Llamamos a la API para obtener la respuesta
                $responseText = $this->generateText($messages);

                // Añadimos la respuesta de la IA para poder mostrarla
                $messages[] = ['role' => 'assistant', 'content' => $responseText];
            } catch (\Exception $e) {
                $responseText = 'Error: ' . $e->getMessage();
            }
        }

        return $this->render('ia.html.twig', [
            'messages' => $messages,
            'response' => $responseText,
            'userMessage' => $userMessage,
        ]);
    }
}
