<?php
/**
 * CategoryController
 * @package admin-product-category
 * @version 0.0.1
 */

namespace AdminProductCategory\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibForm\Library\Combiner;
use LibPagination\Library\Paginator;
use ProductCategory\Model\{
    ProductCategory as PCategory,
    ProductCategoryChain as PCChain
};

class CategoryController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['product', 'category']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_product_category)
            return $this->show404();

        $category = (object)[];

        $id = $this->req->param->id;
        if($id){
            $category = PCategory::getOne(['id'=>$id]);
            if(!$category)
                return $this->show404();
            $params = $this->getParams('Edit Product Category');
        }else{
            $params = $this->getParams('Create New Product Category');
        }

        $form           = new Form('admin.product-category.edit');
        $params['form'] = $form;

        $c_opts = [
            'meta'   => [null, null, 'json'],
            'parent' => [null, null, 'format', 'all', 'name', 'parent']
        ];

        $combiner = new Combiner($id, $c_opts, 'product-category');
        $category = $combiner->prepare($category);

        $params['opts'] = $combiner->getOptions();
        array_unshift($params['opts']['parent'], (object)[
            'value' => 0,
            'label' => 'None',
            'parent' => 0
        ]);

        if($id){
            // remove self from parent
            foreach($params['opts']['parent'] as $index => $parent){
                if($parent->value == $id){
                    unset($params['opts']['parent'][$index]);
                    break;
                }
            }
        }

        if(!($valid = $form->validate($category)) || !$form->csrfTest('noob'))
            return $this->resp('product/category/edit', $params);
        
        $valid = $combiner->finalize($valid);
        if(!isset($valid->parent))
            $valid->parent = 0;

        if($id){
            if(!PCategory::set((array)$valid, ['id'=>$id]))
                deb(PCategory::lastError());
        }else{
            $valid->user = $this->user->id;
            if(!PCategory::create((array)$valid))
                deb(PCategory::lastError());
        }

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => $id ? 2 : 1,
            'type'   => 'product-category',
            'original' => $category,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminProductCategory');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_product_category)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;

        list($page, $rpp) = $this->req->getPager(25, 50);

        $categories = PCategory::get($cond, $rpp, $page, ['name'=>true]) ?? [];
        if($categories)
            $categories = Formatter::formatMany('product-category', $categories, ['user','parent']);
        
        $params               = $this->getParams('Product Category');
        $params['categories'] = $categories;
        $params['form']       = new Form('admin.product-category.index');

        $params['form']->validate( (object)$this->req->get() );

        // pagination
        $params['total'] = $total = PCategory::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminProductCategory'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('product/category/index', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_product_category)
            return $this->show404();

        $id       = $this->req->param->id;
        $category = PCategory::getOne(['id'=>$id]);
        $next     = $this->router->to('adminProductCategory');
        $form     = new Form('admin.product-category.index');

        if(!$category)
            return $this->show404();

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'product-category',
            'original' => $category,
            'changes'  => null
        ]);

        PCategory::remove(['id'=>$id]);
        PCategory::set(['parent'=>0], ['parent'=>$id]);
        PCChain::remove(['category'=>$id]);
        
        $this->res->redirect($next);
    }
}