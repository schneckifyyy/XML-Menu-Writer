<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class XMLDisplayController extends AbstractController
{
    #[Route('/', name: 'xmlTool')]
    public function home(Request $request): Response
    {
        $folder = $this->getParameter('kernel.project_dir') . '/var/xml_files';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        // CREATE NEW XML
        if ($request->isMethod('POST') && $request->request->get('create_xml')) {
            $filename = 'restaurant_menu_' . time() . '.xml';
            $filePath = $folder . '/' . $filename;

            $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<menu>
    <starters>
        <item><name></name><price></price></item>
    </starters>
    <main>
        <item><name></name><price></price></item>
    </main>
    <drinks>
        <item><name></name><price></price></item>
    </drinks>
    <desserts>
        <item><name></name><price></price></item>
    </desserts>
</menu>
XML;

            file_put_contents($filePath, $xmlContent);
            $this->addFlash('success', 'New restaurant menu XML created: ' . $filename);
            return $this->redirectToRoute('xmlTool');
        }

        // EDIT EXISTING XML
        if ($request->isMethod('POST') && $request->request->get('edit_file')) {
            $filename = $request->request->get('file_name');
            $filePath = $folder . '/' . $filename;

            if (file_exists($filePath)) {
                file_put_contents($filePath, $request->request->get('xml_content'));
                $this->addFlash('success', "XML file '$filename' saved successfully.");
            } else {
                $this->addFlash('danger', "File '$filename' does not exist.");
            }

            return $this->redirectToRoute('xmlTool');
        }

        // LIST ALL XML FILES
        $allFiles = scandir($folder);
        $files = array_filter($allFiles, fn($f) => substr($f, -4) === '.xml');

        return $this->render('index.html.twig', ['files' => $files]);
    }

    #[Route('/xml/load/{filename}', name: 'xml_load', requirements: ['filename' => '.+'])]
    public function load(string $filename): JsonResponse
    {
        // Proper path with slash
        $folder = $this->getParameter('kernel.project_dir') . '/var/xml_files';
        $filename = basename($filename); // prevent directory traversal
        $filePath = $folder . '/' . $filename;

        if (!file_exists($filePath)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        return $this->json([
            'content' => file_get_contents($filePath)
        ]);
    }
}

