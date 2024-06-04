<?php

namespace App\Controller;

use App\Entity\Reserva;
use App\Form\ReservaType;
use App\Repository\ReservaRepository;
use App\Repository\InstalacionRepository;
use App\Service\EmailService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reservas')]
class ReservaController extends AbstractController {
  private $reservaRepository;
  private $entityManager;
  private $userService;
  private $emailService;

  public function __construct(ReservaRepository $reservaRepository, EntityManagerInterface $entityManager, UserService $userService, EmailService $emailService) {
    $this->reservaRepository = $reservaRepository;
    $this->entityManager = $entityManager;
    $this->userService = $userService;
    $this->emailService = $emailService;
  }

  #[Route('/all', name: 'app_reserva_index', methods: ['GET'])]
  public function index(ReservaRepository $reservaRepository): JsonResponse {
    $reservas = $reservaRepository->findAll();
    $reservasArray = [];

    foreach ($reservas as $reserva) {
      $reservasArray[] = [
        'id' => $reserva->getId(),
        'fechaYHora' => $reserva->getFechaYHora()->format('c'),
        'duracion' => $reserva->getDuracion(),
        'importe' => $reserva->getImporte(),
        'idUsuario' => $reserva->getIdUsuario()->getId(),
        'idInstalacion' => $reserva->getIdInstalacion()->getId(),
      ];
    }

    return $this->json(['ok' => 'Todo ha ido correctamente', "results" => $reservasArray]);
  }

  #[Route('/userEmail', name: 'app_reservas_usuario', methods: ['GET'])]
  public function showUserReservations(InstalacionRepository $instalacionRepository): JsonResponse {

    /** @var \App\Entity\User $user */
    $user = $this->getUser();

    $reservas = $this->reservaRepository->findBy(['idUsuario' => $user->getId()]);

    if (count($reservas) > 0) {
      $reservasJSON = [];
      foreach ($reservas as $reserva) {
        $instalacion = $instalacionRepository->find($reserva->getIdInstalacion()->getId());
        $reservasJSON[] = [
          'id' => $reserva->getId(),
          'fechaYHora' => $reserva->getFechaYHora()->format('c'),
          'duracion' => $reserva->getDuracion(),
          'importe' => $reserva->getImporte(),
          'idUsuario' => $reserva->getIdUsuario()->getId(),
          'idInstalacion' => $reserva->getIdInstalacion()->getId(),
          'nombreInstalacion' => $instalacion->getNombre(),
        ];
      }
      return $this->json(["ok" => 'Todo ha ido correcto', 'results' => $reservasJSON]);
    }

    return $this->json(['ok' => 'No hay reservas para el usuario con email ', 'results' => []]);
  }

  #[Route('/idInstalacion/{idInstalacion}', name: 'app_reservationsByInstallation', methods: ['GET'])]
  public function showReservationsByInstallation($idInstalacion, InstalacionRepository $instalacionRepository): JsonResponse {
    $reservas = $this->reservaRepository->findBy(['idInstalacion' => $idInstalacion]);

    if (count($reservas) > 0) {
      $reservasJSON = [];
      foreach ($reservas as $reserva) {
        $instalacion = $instalacionRepository->find($reserva->getIdInstalacion()->getId());
        $reservasJSON[] = [
          'id' => $reserva->getId(),
          'fechaYHora' => $reserva->getFechaYHora()->format('c'),
          'duracion' => $reserva->getDuracion(),
          'importe' => $reserva->getImporte(),
          'idUsuario' => $reserva->getIdUsuario()->getId(),
          'idInstalacion' => $reserva->getIdInstalacion()->getId(),
          'nombreInstalacion' => $instalacion->getNombre(),
        ];
      }
      return $this->json(["ok" => 'Todo ha ido correcto', 'results' => $reservasJSON]);
    }

    return $this->json(['ok' => 'No hay reservas para el usuario con email ', 'results' => 0]);
  }

  #[Route('/new', name: 'app_reserva_new', methods: ['GET', 'POST'])]
  public function new(Request $request, InstalacionRepository $instalacionRepository): JsonResponse {
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
    $exists = $this->checkIfAReservationExists($fechaYHora, $duracion, $idInstalacion);
    if ($exists) {
      return $this->json(['error' => 'Ya existe una reserva para esa instalación en esa fecha y hora'], Response::HTTP_CONFLICT);
    }

    // Si no hemos lanzado ningún error, está OK, entonces lo insertamos en la BD
    $reserva->setFechaYHora($fechaYHora); // hay que pasarle la fecha y hora juntas (DateTime
    $reserva->setDuracion($duracion);
    $reserva->setImporte($importe);
    $reserva->setIdUsuario($usuarioActual); // hay que pasarle el objeto usuario completo
    $reserva->setIdInstalacion($instalacion); // hay que pasarle el objeto instalación completo

    $this->entityManager->persist($reserva);
    $this->entityManager->flush();

    return $this->json(['ok' => 'Reserva creada correctamente']);
  }

  #[Route('/delete/{id}', name: 'app_reserva_delete', methods: ['POST'])]
  public function delete($id): Response {
    $reserva = $this->reservaRepository->find($id);

    if (!$reserva) {
      return $this->json(['error' => "La reserva con id <{$id}> no existe"], 404);
    }

    // Comprueba si el usuario actual tiene permiso para eliminar la reserva (si es admin puede)
    /** @var \App\Entity\User $usuarioActual */
    $usuarioActual = $this->getUser();

    if ($reserva->getIdUsuario()->getId() !== $usuarioActual->getId() && $this->userService->isAdmin() === false) {
      return $this->json(['error' => "No tienes permiso para eliminar esta reserva"], 403);
    }

    // TODO FALTA REVISAR ESTO
    if ($usuarioActual->getId() !== $reserva->getIdUsuario()->getId()) {
      $this->emailService->sendUpdateReservationEmail($reserva->getIdUsuario()->getEmail(), $reserva);
    }

    $this->entityManager->remove($reserva);
    $this->entityManager->flush();

    return $this->json(['ok' => "Todo correcto. Reserva <{$reserva->getId()}> eliminada correctamente"]);
  }

  #[Route('/edit/{id}', name: 'app_reserva_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Reserva $reserva, InstalacionRepository $instalacionRepository): JsonResponse {
    $dataBody = json_decode($request->getContent(), true);

    $fechaYHora = new \DateTime($dataBody['fecha'] . ' ' . $dataBody['hora']);
    $duracion = $dataBody['duracion'];
    $importe = $dataBody['importe'];
    $idInstalacion = $dataBody['idInstalacion'];

    $exists = $this->checkIfAReservationExists($fechaYHora, $duracion, $idInstalacion, $reserva->getId());
    $instalacion = $instalacionRepository->find($idInstalacion);

    if ($exists) {
      return $this->json(['error' => 'Ya existe una reserva para esa instalación en esa fecha y hora'], Response::HTTP_CONFLICT);
    }

    $reserva->setFechaYHora($fechaYHora);
    $reserva->setDuracion($duracion);
    $reserva->setImporte($importe);
    $reserva->setIdInstalacion($instalacion);

    $this->entityManager->flush();

    return $this->json(['ok' => "Reserva {$reserva->getId()} actualizada correctamente"]);
  }

  // Util functions
  private function checkIfAReservationExists(\DateTime $fechaYHora, string $duracion, string $idInstalacion, int $idReserva = null): bool {
    $fechaInicioComprobacion = (clone $fechaYHora)->modify('-60 minutes');
    $fechaFinComprobacion = (clone $fechaYHora)->modify("+$duracion minutes");
    $reservasExistentes = $this->reservaRepository->findReservasByDayAndHour($fechaInicioComprobacion, $fechaFinComprobacion, $idInstalacion);

    // En caso de EDITAR, tenemos que quitar el id de la reserva que se está editanto
    if ($idReserva) {
      $reservasExistentes = array_filter($reservasExistentes, function ($reserva) use ($idReserva) {
        return $reserva->getId() !== $idReserva;
      });
    }

    if (count($reservasExistentes) > 0) {
      // aquí tendríamos que comprobar si la duración de la reserva anterior es 60min (en caso de ser 60 no habría problema) pero si es 90min o más, no se podría hacer la reserva
      foreach ($reservasExistentes as $reservaExist) {
        $horaInicioExistente = $reservaExist->getFechaYHora()->getTimestamp();
        $horaFinExistente = (clone $reservaExist->getFechaYHora())->modify("+{$reservaExist->getDuracion()} minutes")->getTimestamp();
        $horaInicioNueva = $fechaYHora->getTimestamp();
        $horaFinNueva = $fechaFinComprobacion->getTimestamp();
        // - Hora de la antigua reserva === Hora de la nueva reserva
        if ($horaInicioExistente == $horaInicioNueva) {
          return true;
        }
        // - Hora de la antigua reserva < Hora de la nueva reserva && hora fin existente > hora inicio nueva
        if ($horaInicioExistente < $horaInicioNueva && $horaFinExistente > $horaInicioNueva) {
          return true;
        }
        // - Hora de la antigua reserva > Hora de la nueva reserva && dura > 60
        if ($horaInicioExistente > $horaInicioNueva && $horaFinNueva > $horaInicioExistente) {
          return true;
        }
      }
    }
    return false;
  }

}

/* TODO
- Editar el método de DELETE reservas, la ruta habrá que modificarla ahora mismo está /id
- Repasar método EDIT
- Utilizar CARBON librería de PHP para trabajar con fechas??
*/

/*  POR SI A CASO
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
 return $this->json(['ok' => 'Reserva encontrada', "results" => $reservaInfo]);
}

#[Route('/{id}/edit', name: 'app_reserva_edit', methods: ['GET', 'POST'])]
 public function edit(Request $request, Reserva $reserva): JsonResponse {
   $dataBody = json_decode($request->getContent(), true);

   $fechaYHora = new \DateTime($dataBody['fecha'] . ' ' . $dataBody['hora']);
   $duracion = $dataBody['duracion'];
   $importe = $dataBody['importe'];
   $idInstalacion = $dataBody['idInstalacion'];

   $reserva->setFechaYHora($fechaYHora);
   $reserva->setDuracion($duracion);
   $reserva->setImporte($importe);
   $reserva->setIdInstalacion($idInstalacion);

   $this->entityManager->flush();

   return $this->json(['message' => "Reserva <{$reserva->getId()}> actualizada correctamente"]);
 }*/