<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Generate a 6-digit OTP
                $otp = sprintf('%06d', random_int(0, 999999));
                $user->setOtpCode($otp);
                $user->setOtpExpiresAt((new \DateTimeImmutable())->modify('+15 minutes'));
                $em->flush();

                // IN A REAL APP: Send $otp via Email to $email
                // For this demo, we will just flash it to the screen so the user can easily copy it
                $this->addFlash('success', 'OTP simulated email: Your OTP is ' . $otp);
                
                $request->getSession()->set('reset_email', $email);
                return $this->redirectToRoute('app_verify_otp');
            }

            $this->addFlash('danger', 'No account found for that email address.');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/verify-otp', name: 'app_verify_otp')]
    public function verifyOtp(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $email = $request->getSession()->get('reset_email');
        if (!$email) {
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $otp = $request->request->get('otp');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user && $user->getOtpCode() === $otp) {
                if ($user->getOtpExpiresAt() > new \DateTimeImmutable()) {
                    $request->getSession()->set('can_reset_password', true);
                    return $this->redirectToRoute('app_reset_password');
                } else {
                    $this->addFlash('danger', 'OTP has expired. Please request a new one.');
                }
            } else {
                $this->addFlash('danger', 'Invalid OTP.');
            }
        }

        return $this->render('security/verify_otp.html.twig', ['email' => $email]);
    }

    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('can_reset_password')) {
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $email = $request->getSession()->get('reset_email');
            $user = $userRepository->findOneBy(['email' => $email]);
            $newPassword = $request->request->get('password');

            if ($user && $newPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $user->setOtpCode(null);
                $user->setOtpExpiresAt(null);
                $em->flush();

                $request->getSession()->remove('reset_email');
                $request->getSession()->remove('can_reset_password');

                $this->addFlash('success', 'Your password has been successfully reset. Please log in.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig');
    }
}
