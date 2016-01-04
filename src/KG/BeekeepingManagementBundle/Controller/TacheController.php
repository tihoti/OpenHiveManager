<?php

/* 
 * Copyright (C) 2015 Kévin Grenèche < kevin.greneche at openhivemanager.org >
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace KG\BeekeepingManagementBundle\Controller;
use KG\BeekeepingManagementBundle\Entity\Tache;
use KG\BeekeepingManagementBundle\Entity\Colonie;
use KG\BeekeepingManagementBundle\Form\Type\TacheType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
class TacheController extends Controller
{
    
    /**
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("tache", options={"mapping": {"tache_id" : "id"}}) 
    */    
    public function deleteAction(Tache $tache)
    {
        $exploitation = $tache->getColonie()->getRuche()->getRucher()->getExploitation();
        $apiculteurExploitations = $exploitation->getApiculteurExploitations();
        $not_permitted = true;
        
        foreach ( $apiculteurExploitations as $apiculteurExploitation ){
            if( $apiculteurExploitation->getApiculteur()->getId() == $this->getUser()->getId() ){
                $not_permitted = false;
                break;
            }
        }
        
        if( $not_permitted || $tache->getVisite() ){
            throw new NotFoundHttpException('Page inexistante.');
        }

        $em = $this->getDoctrine()->getManager();
              
        $em->remove($tache);
        $em->flush();

        $flash = $this->get('braincrafted_bootstrap.flash');
        $flash->success('Tâche supprimée avec succès');

        return $this->redirect($this->generateUrl('kg_beekeeping_management_view_ruche', array('ruche_id' => $tache->getColonie()->getRuche()->getId())));            
    }
    
    /**
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("tache", options={"mapping": {"tache_id" : "id"}}) 
    */    
    public function viewAction(Tache $tache)
    {
        $apiculteurExploitations = $tache->getColonie()->getRuche()->getRucher()->getExploitation()->getApiculteurExploitations();
        $not_permitted = true;
        
        foreach ( $apiculteurExploitations as $apiculteurExploitation ){
            if( $apiculteurExploitation->getApiculteur()->getId() == $this->getUser()->getId() ){
                $not_permitted = false;
                break;
            }
        }
        
        if( $not_permitted ){
            throw new NotFoundHttpException('Page inexistante.');
        }
       
        return $this->render('KGBeekeepingManagementBundle:Tache:view.html.twig', 
                array(  'tache' => $tache ));
    }
    
    /**
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("colonie", options={"mapping": {"colonie_id" : "id"}})  
    */    
    public function addAction(Colonie $colonie, Request $request)
    {
        $exploitation = $colonie->getRuche()->getRucher()->getExploitation();
        $apiculteurExploitations = $exploitation->getApiculteurExploitations();
        $not_permitted = true;
        
        foreach ( $apiculteurExploitations as $apiculteurExploitation ){
            if( $apiculteurExploitation->getApiculteur()->getId() == $this->getUser()->getId() ){
                $not_permitted = false;
                break;
            }
        }

        if( $not_permitted || $colonie->getMorte()){
            throw new NotFoundHttpException('Page inexistante.');
        }       
        
        $tache = new Tache($colonie);
        
        $form = $this->createForm(new TacheType, $tache);
        
        if ($form->handleRequest($request)->isValid()){
                   
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($tache);
            $em->flush();
        
            $flash = $this->get('braincrafted_bootstrap.flash');
            $flash->success('Tâche créée avec succès');
        
            return $this->redirect($this->generateUrl('kg_beekeeping_management_view_ruche', array('ruche_id' => $tache->getColonie()->getRuche()->getId())));
        }
        return $this->render('KGBeekeepingManagementBundle:Tache:add.html.twig', 
                             array(
                                    'form'    => $form->createView(),
                                    'colonie' => $colonie
                ));        
    }
    
    /**
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("tache", options={"mapping": {"tache_id" : "id"}}) 
    */    
    public function updateAction(Tache $tache, Request $request)
    {
        $apiculteurExploitations = $tache->getColonie()->getRuche()->getRucher()->getExploitation()->getApiculteurExploitations();
        $not_permitted = true;
        
        foreach ( $apiculteurExploitations as $apiculteurExploitation ){
            if( $apiculteurExploitation->getApiculteur()->getId() == $this->getUser()->getId() ){
                $not_permitted = false;
                break;
            }
        }
        
        if( $not_permitted || $tache->getColonie()->getMorte() || $tache->getVisite() ){
            throw new NotFoundHttpException('Page inexistante.');
        }
        
        $form = $this->createForm(new TacheType, $tache);
        
        if ($form->handleRequest($request)->isValid()){
             
            $em = $this->getDoctrine()->getManager();

            $em->persist($tache);
            $em->flush();
        
            $flash = $this->get('braincrafted_bootstrap.flash');
            $flash->success('Tâche mise à jour avec succès');
        
            return $this->redirect($this->generateUrl('kg_beekeeping_management_view_ruche', array('ruche_id' => $tache->getColonie()->getRuche()->getId())));
        }
        return $this->render('KGBeekeepingManagementBundle:Tache:update.html.twig', 
                             array(
                                    'form'  => $form->createView(),
                                    'tache' => $tache
                ));
    }
    
    /**
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("colonie", options={"mapping": {"colonie_id" : "id"}})  
    */    
    /*public function viewAllAction(Request $request, Colonie $colonie, $page)
    {
        $exploitation = $colonie->getRuche()->getRucher()->getExploitation();
        $apiculteurExploitations = $exploitation->getApiculteurExploitations();
        $not_permitted = true;
        
        foreach ( $apiculteurExploitations as $apiculteurExploitation ){
            if( $apiculteurExploitation->getApiculteur()->getId() == $this->getUser()->getId() ){
                $not_permitted = false;
                break;
            }
        }
        
        if( $not_permitted || $page < 1  || $colonie->getVisites()->isEmpty()){
            throw new NotFoundHttpException('Page inexistante.');
        }      
        
        if($colonie){    
            $query = $this->getDoctrine()->getRepository('KGBeekeepingManagementBundle:Visite')->getListByColonie($colonie);    
        }
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', $page),
            30,
            array(
                'defaultSortFieldName' => 'visite.date',
                'defaultSortDirection' => 'desc'
            )                
        );        
        
        return $this->render('KGBeekeepingManagementBundle:Visite:viewAll.html.twig', 
                array(  'colonie'    => $colonie,
                        'pagination' => $pagination));
    }*/    
    
}