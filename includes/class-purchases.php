<?php
if (!defined('ABSPATH')) {
    exit;
}

class BYOT_Acc_Purchases {
    public static function render() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Acces interzis.', 'byot-accounting'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : 'list';
        if ($action === 'add' || $action === 'edit') {
            self::render_form();
            return;
        }

        if (isset($_GET['delete'])) {
            self::handle_delete();
        }

        self::render_list();
    }

    private static function handle_delete() {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'byot_delete_purchase')) {
            wp_die(esc_html__('Securitate.', 'byot-accounting'));
        }
        global $wpdb;
        $id = isset($_GET['delete']) ? intval($_GET['delete']) : 0;
        $wpdb->delete($wpdb->prefix . 'byot_purchases', array('id' => $id));
        wp_safe_redirect(admin_url('admin.php?page=byot-purchases&deleted=1'));
        exit;
    }

    private static function handle_save() {
        if (!isset($_POST['byot_purchase_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['byot_purchase_nonce'])), 'byot_save_purchase')) {
            wp_die(esc_html__('Nonce invalid.', 'byot-accounting'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'byot_purchases';
        $qty = isset($_POST['quantity']) ? floatval(sanitize_text_field(wp_unslash($_POST['quantity']))) : 0;
        $unit = isset($_POST['unit_price']) ? floatval(sanitize_text_field(wp_unslash($_POST['unit_price']))) : 0;
        $data = array(
            'purchase_date'   => isset($_POST['purchase_date']) ? sanitize_text_field(wp_unslash($_POST['purchase_date'])) : '',
            'product_name'    => isset($_POST['product_name']) ? sanitize_text_field(wp_unslash($_POST['product_name'])) : '',
            'wc_product_id'   => isset($_POST['wc_product_id']) ? intval($_POST['wc_product_id']) : 0,
            'quantity'        => $qty,
            'unit_price'      => $unit,
            'total_amount'    => $qty * $unit,
            'supplier'        => isset($_POST['supplier']) ? sanitize_text_field(wp_unslash($_POST['supplier'])) : '',
            'notes'           => isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '',
        );
        $id = isset($_POST['purchase_id']) ? intval($_POST['purchase_id']) : 0;

        if ($id > 0) {
            $wpdb->update($table, $data, array('id' => $id));
        } else {
            $wpdb->insert($table, $data);
        }

        wp_safe_redirect(admin_url('admin.php?page=byot-purchases&saved=1'));
        exit;
    }

    private static function render_form() {
        if (!empty($_POST['save_purchase'])) {
            self::handle_save();
        }

        global $wpdb;
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $item = new stdClass();
        if ($id > 0) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}byot_purchases WHERE id = %d", $id));
            if (!$item) {
                $item = new stdClass();
            }
        }

        $products = wc_get_products(array('limit' => -1, 'status' => 'publish'));
        ?>
        <div class="wrap">
            <h1><?php echo $id ? esc_html__('Editeaza Achizitie', 'byot-accounting') : esc_html__('Adauga Achizitie', 'byot-accounting'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('byot_save_purchase', 'byot_purchase_nonce'); ?>
                <input type="hidden" name="purchase_id" value="<?php echo esc_attr($id); ?>">
                <table class="form-table">
                    <tr>
                        <th><label for="purchase_date"><?php esc_html_e('Data', 'byot-accounting'); ?></label></th>
                        <td><input type="date" id="purchase_date" name="purchase_date" value="<?php echo esc_attr($item->purchase_date ?? date('Y-m-d')); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label for="product_name"><?php esc_html_e('Produs', 'byot-accounting'); ?></label></th>
                        <td><input type="text" id="product_name" name="product_name" value="<?php echo esc_attr($item->product_name ?? ''); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label for="wc_product_id"><?php esc_html_e('Produs WooCommerce (optional)', 'byot-accounting'); ?></label></th>
                        <td>
                            <select id="wc_product_id" name="wc_product_id">
                                <option value="0"><?php esc_html_e('-- Selecteaza --', 'byot-accounting'); ?></option>
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected($item->wc_product_id ?? 0, $product->get_id()); ?>>
                                        <?php echo esc_html($product->get_name()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="quantity"><?php esc_html_e('Cantitate', 'byot-accounting'); ?></label></th>
                        <td><input type="number" step="0.01" id="quantity" name="quantity" value="<?php echo esc_attr($item->quantity ?? 1); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label for="unit_price"><?php esc_html_e('Pret unitar', 'byot-accounting'); ?></label></th>
                        <td><input type="number" step="0.01" id="unit_price" name="unit_price" value="<?php echo esc_attr($item->unit_price ?? ''); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label for="supplier"><?php esc_html_e('Furnizor', 'byot-accounting'); ?></label></th>
                        <td><input type="text" id="supplier" name="supplier" value="<?php echo esc_attr($item->supplier ?? ''); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="notes"><?php esc_html_e('Notite', 'byot-accounting'); ?></label></th>
                        <td><textarea id="notes" name="notes" rows="4" cols="50"><?php echo esc_textarea($item->notes ?? ''); ?></textarea></td>
                    </tr>
                </table>
                <?php submit_button(__('Salveaza', 'byot-accounting'), 'primary', 'save_purchase'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=byot-purchases')); ?>" class="button"><?php esc_html_e('Inapoi', 'byot-accounting'); ?></a>
            </form>
        </div>
        <?php
    }

    private static function render_list() {
        global $wpdb;
        $table = $wpdb->prefix . 'byot_purchases';
        $items = $wpdb->get_results("SELECT * FROM {$table} ORDER BY purchase_date DESC LIMIT 100");
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=byot-purchases&action=add')); ?>" class="page-title-action"><?php esc_html_e('Adauga noua', 'byot-accounting'); ?></a>
            </h1>
            <?php if (isset($_GET['saved'])) : ?>
                <div class="notice notice-success"><p><?php esc_html_e('Salvat cu succes.', 'byot-accounting'); ?></p></div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])) : ?>
                <div class="notice notice-success"><p><?php esc_html_e('Sters cu succes.', 'byot-accounting'); ?></p></div>
            <?php endif; ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'byot-accounting'); ?></th>
                        <th><?php esc_html_e('Data', 'byot-accounting'); ?></th>
                        <th><?php esc_html_e('Produs', 'byot-accounting'); ?></th>
                        <th><?php esc_html_e('Cantitate', 'byot-accounting'); ?></th>
                        <th><?php esc_html_e('Pret unitar', 'byot-accounting'); ?></th>
                        <th><?php esc_html_e('Total', 'byot-accounting'); ?></th>
                        <th><?php esc_html_e('Furnizor', 'byot-accounting'); ?></th>
                        <th><?php esc_html_e('Actiuni', 'byot-accounting'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)) : ?>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td><?php echo esc_html($item->id); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($item->purchase_date))); ?></td>
                                <td><?php echo esc_html($item->product_name); ?></td>
                                <td><?php echo esc_html($item->quantity); ?></td>
                                <td><?php echo wp_kses_post(wc_price($item->unit_price)); ?></td>
                                <td><?php echo wp_kses_post(wc_price($item->total_amount)); ?></td>
                                <td><?php echo esc_html($item->supplier); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=byot-purchases&action=edit&id=' . $item->id)); ?>"><?php esc_html_e('Editeaza', 'byot-accounting'); ?></a> |
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=byot-purchases&delete=' . $item->id), 'byot_delete_purchase')); ?>" onclick="return confirm('<?php esc_attr_e('Sigur stergi?', 'byot-accounting'); ?>')"><?php esc_html_e('Sterge', 'byot-accounting'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="8"><?php esc_html_e('Nu exista inregistrari.', 'byot-accounting'); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}