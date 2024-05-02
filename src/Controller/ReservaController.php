<?php

namespace App\Controller;

use App\Entity\Reserva;
use App\Form\ReservaType;
use App\Repository\ReservaRepository;
use App\Repository\InstalacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reservas')]
class ReservaController extends AbstractController {
  #[Route('/all', name: 'app_reserva_index', methods: ['GET'])]
  public function index(ReservaRepository $reservaRepository): JsonResponse {
    $reservas = $reservaRepository->findAll();
    $reservasArray = [];

    foreach ($reservas as $reserva) {
      $reservasArray[] = [
        'id' => $reserva->getId(),
        'fecha' => $reserva->getFechaYHora()->format('Y-m-d'),
        'hora' => $reserva->getFechaYHora()->format('H:i'),
        'duracion' => $reserva->getDuracion(),
        'importe' => $reserva->getImporte(),
        'idUsuario' => $reserva->getIdUsuario()->getId(),
        'idInstalacion' => $reserva->getIdInstalacion()->getId(),
      ];
    }

    return $this->json(["reservas" => $reservasArray]);
  }

  #[Route('/new', name: 'app_reserva_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager, ReservaRepository $reservaRepository, InstalacionRepository $instalacionRepository): JsonResponse {
    $reserva = new Reserva();
    // "type hinting" a tu objeto $usuarioActual como instancia de tu clase User. Esto le indicará a tu IDE que el objeto es una instancia de User y debería permitirte acceder a los métodos definidos en esa clase.
    /** @var \App\Entity\User $usuarioActual */
    $usuarioActual = $this->getUser();

    $dataBody = json_decode($request->getContent(), true);

    $fechaYHora = new \DateTime($dataBody['fecha'] . ' ' . $dataBody['hora']);
    $importe = $dataBody['importe'];
    $duracion = $dataBody['duracion'];
    $idInstalacion = $dataBody['idInstalacion'];
    $instalacion = $instalacionRepository->find($idInstalacion);

    // Compruebo si ya existe una reserva para esa instalación en esa fecha y hora
    $fechaInicioComprobacion = (clone $fechaYHora)->modify('-60 minutes');
    $fechaFinComprobacion = (clone $fechaYHora)->modify("+$duracion minutes");
    $reservasExistentes = $reservaRepository->findReservasByDayAndHour($fechaInicioComprobacion, $fechaFinComprobacion, $idInstalacion);

    if (count($reservasExistentes) > 0) {
      // aquí tendríamos que comprobar si la duración de la reserva anterior es 60min (en caso de ser 60 no habría problema) pero si es 90min o más, no se podría hacer la reserva
      foreach ($reservasExistentes as $reservaExist) {
        $horaInicioExistente = $reservaExist->getFechaYHora()->getTimestamp();
        $horaFinExistente = (clone $reservaExist->getFechaYHora())->modify("+{$reservaExist->getDuracion()} minutes")->getTimestamp();
        $horaInicioNueva = $fechaYHora->getTimestamp();
        $horaFinNueva = $fechaFinComprobacion->getTimestamp();
        // - Hora de la antigua reserva === Hora de la nueva reserva
        if ($horaInicioExistente == $horaInicioNueva) {
          return $this->json(['message' => 'Ya existe una reserva para esa instalación en esa fecha y hora'], Response::HTTP_CONFLICT);
        }
        // - Hora de la antigua reserva < Hora de la nueva reserva && hora fin existente > hora inicio nueva
        if ($horaInicioExistente < $horaInicioNueva && $horaFinExistente > $horaInicioNueva) {
          return $this->json(['message' => 'Ya existe una reserva para esa instalación en esa fecha y hora'], Response::HTTP_CONFLICT);
        }
        // - Hora de la antigua reserva > Hora de la nueva reserva && dura > 60
        if ($horaInicioExistente > $horaInicioNueva && $horaFinNueva > $horaInicioExistente) {
          return $this->json(['message' => 'Ya existe una reserva para esa instalación en esa fecha y hora'], Response::HTTP_CONFLICT);
        }
      }
    }

    // Si no hemos lanzado ningún error todo está OK, entonces lo insertamos en la BD
    $reserva->setFechaYHora($fechaYHora); // hay que pasarle la fecha y hora juntas (DateTime
    $reserva->setDuracion($duracion);
    $reserva->setImporte($importe);
    $reserva->setIdUsuario($usuarioActual); // hay que pasarle el objeto usuario completo
    $reserva->setIdInstalacion($instalacion); // hay que pasarle el objeto instalación completo

    $entityManager->persist($reserva);
    $entityManager->flush();

    return $this->json(['message' => 'Reserva creada correctamente']);
  }
  // Para probar el new Reserva he hecho
  // 13.30 - 90min X
  // 13.30 - 60 min V
  // 14.00 - 60/90 min X
  // 15.00 - 60/90 min X
  // 15.30 - 60/90 min X
  // 16.00 - 60/90 min X

  #[Route('/{id}', name: 'app_reserva_show', methods: ['GET'])]
  public function show(Reserva $reserva): JsonResponse {
    $reservaInfo = [
      'id' => $reserva->getId(),
      'fecha' => $reserva->getFechaYHora()->format('Y-m-d'),
      'hora' => $reserva->getFechaYHora()->format('H:i'),
      'duracion' => $reserva->getDuracion(),
      'importe' => $reserva->getImporte(),
      'idUsuario' => $reserva->getIdUsuario()->getId(),
      'idInstalacion' => $reserva->getIdInstalacion()->getId(),
    ];
    return $this->json(["reserva" => $reservaInfo]);
  }

  #[Route('/{id}/edit', name: 'app_reserva_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Reserva $reserva, EntityManagerInterface $entityManager): JsonResponse {
    $dataBody = json_decode($request->getContent(), true);

    $fechaYHora = new \DateTime($dataBody['fecha'] . ' ' . $dataBody['hora']);
    $duracion = $dataBody['duracion'];
    $importe = $dataBody['importe'];
    $idInstalacion = $dataBody['idInstalacion'];

    $reserva->setFechaYHora($fechaYHora);
    $reserva->setDuracion($duracion);
    $reserva->setImporte($importe);
    $reserva->setIdInstalacion($idInstalacion);

    $entityManager->flush();

    return $this->json(['message' => "Reserva <{$reserva->getId()}> actualizada correctamente"]);
  }

  #[Route('/{id}', name: 'app_reserva_delete', methods: ['POST'])]
  public function delete(Request $request, Reserva $reserva, EntityManagerInterface $entityManager): Response {
    if ($this->isCsrfTokenValid('delete' . $reserva->getId(), $request->request->get('_token'))) {
      $entityManager->remove($reserva);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_reserva_index', [], Response::HTTP_SEE_OTHER);
  }
}

/* TODO
- Editar el método de DELETE reservas, la ruta habrá que modificarla ahora mismo está /id
- Repasar método EDIT
*/
