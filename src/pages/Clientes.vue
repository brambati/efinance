<template>
    <q-page class="row q-pa-md">
    
        <div class="col-md-4 q-mb-md">
            <q-btn color="primary" label="Adicionar Cidade" @click="cadastrar" />
        </div>
    
        <div class="col-md-12">
            <q-table title="Lista de Clientes" :rows="rows" :columns="columns" :pagination="pagination" row-key="name" ref="table" v-if="rows.length > 0">
                <template #body-cell-acao="props">
                      <q-td :props="props">
                        <q-btn-dropdown color="primary" label="Ação" dense>
                          <q-list>
                            <q-item clickable v-close-popup @click="atualizar(props.row.idCliente, props.row.nome, props.row.email, props.row.telefone, props.row.idCidade, props.row.cidade)">
                              <q-item-section>
                                <q-item-label>Atualizar</q-item-label>
                              </q-item-section>
                            </q-item>
    
                            <q-item clickable v-close-popup @click="excluir(props.row.idCliente, props.row.nome)">
                              <q-item-section>
                                <q-item-label>Excluir</q-item-label>
                              </q-item-section>
                            </q-item>
                          </q-list>
                        </q-btn-dropdown>
                      </q-td>
                </template>
          </q-table>
        </div>
    </q-page>


    <q-dialog v-model="showDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section>
          <div class="text-h6">{{!idCliente ? 'Cadastrar' : 'Atualizar'}} Cliente</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input v-model="idCliente" label="ID" v-if="idCliente" disable />
          <q-input v-model="clienteNome" label="Nome" />
          <q-input v-model="clienteEmail" type="email" label="E-mail">
            <template v-slot:prepend>
              <q-icon name="mail" />
            </template>
          </q-input>
          <q-input v-model="clienteTelefone" label="Telefone" mask="(##) #####-####" fill-mask>
            <template v-slot:prepend>
            <q-icon name="phone" />
          </template>
          </q-input>
          <q-select v-model="cidadeSelect" :options="listaCidade" label="Cidade" />
        </q-card-section>

        <q-card-actions align="right" class="bg-white text-teal">
          <q-btn label="Salvar" color="primary" @click="salvar" />
          <q-btn flat label="Fechar" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { api } from 'boot/axios';

interface resposeData {
  status: string,
  message: string,
  list: Array<{idCliente: number, nome: string, email: string, telefone: string, idCidade: number, cidade: string}>
}

interface resposeDataCidade {
  status: string,
  message: string,
  list: Array<{idCidade: number, cidade: string, idUF: number, estado: string}>
}


interface rowsType {
  idCliente: number, 
  nome: string, 
	email: string, 
	telefone: string, 
  idCidade: number,
  cidade: string
}

interface listaCidade {
  value: number,
  label: string
}

export default defineComponent({
    name: 'PageClientes',
    components: {},
     
    data() {
      const $q = useQuasar();

      return {
          showDialog: ref(false),
          idCliente: 0,
          clienteNome: ref(''),
          clienteEmail: ref(''),
          clienteTelefone: ref(''),
          listaCidade: [] as Array<listaCidade>,
          cidadeSelect: {label: '', value: 0},
          pagination: {
              sortBy: 'desc',
              descending: false,
              page: 1,
              rowsPerPage: 100
          },
          rows: [] as Array<rowsType>,
          columns: [
              { name: 'acao', align: 'left', label: '', field: 'acao' },
              { name: 'idCliente', align: 'left', label: 'ID', field: 'idCliente', sortable: true },
              { name: 'nome', align: 'left', label: 'Nome', field: 'nome', sortable: true },
              { name: 'email', align: 'left', label: 'E-mail', field: 'email', sortable: true },
              { name: 'telefone', align: 'left', label: 'Telefone', field: 'telefone', sortable: true },
              { name: 'cidade', align: 'left', label: 'Cidade', field: 'cidade', sortable: true }
          ],
      };
    },

    async mounted() {
      await this.getData();    
    },

    methods: {
      async atualizar(id: number, nome: string, email: string, telefone: string, idCidade: number, cidade: string) {
          this.idCliente        = id;
          this.clienteNome      = nome;
          this.clienteEmail     = email;
          this.clienteTelefone  = telefone;
          await this.getCidade();
          this.cidadeSelect = {label: cidade, value: idCidade};
          this.showDialog = true
      },

      excluir(id: number, cliente: string) {
        this.$q.dialog({
          title: 'Ação Necessária',
          message: `Deseja realmente excluir o(a) cliente <strong>${cliente}</strong>`,
          cancel: true,
          persistent: true,
          html: true
        }).onOk(() => {
          api.get(`/cliente/delete.php?id=${id}`)
          .then((response) => {

            let data: resposeData = response.data as resposeData;

            if(data.status == 'success') {
              this.showNotif(data.message, 'grey-8');
              void this.getData();
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Loading failed', 'negative'); 
          })
        }).onOk(() => {
          // console.log('>>>> second OK catcher')
        }).onCancel(() => {
          // console.log('>>>> Cancel')
        }).onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        })
      },

      salvar() {

        if(this.clienteNome.length <= 0) {
           this.showNotif('O campo nome deve ser preenchido', 'negative');
           return;
        }

        if(this.clienteEmail.indexOf('@') == -1 || this.clienteEmail.indexOf('.com') == -1) {
           this.showNotif('Campo e-mail inválido', 'negative');
           return;
        }
  
        if(this.clienteTelefone.replace(/[^0-9]/g,'').length < 11) {
           this.showNotif('Você precisa informar o telefone do cliente', 'negative');
           return;
        }       

        if(this.cidadeSelect.label.length <= 2) {
          this.showNotif('O campo cidade deve ser preenchido', 'negative');
          return;
        }

        api.post('/cliente/save.php', { 
            id: this.idCliente, 
            nome: this.clienteNome, 
            email: this.clienteEmail,
            telefone: this.clienteTelefone,
            cidade: this.cidadeSelect.value })
          .then((response) => {

            let data: resposeData = response.data as resposeData;

            if(data.status == 'success') {
              this.showNotif(data.message, 'grey-8');

              if(this.idCliente == 0) {
                this.idCliente = 0;
                this.clienteNome = '';
                this.clienteEmail = '';
                this.clienteTelefone = '';
                this.cidadeSelect = {label: '', value: 0};
              }
              void this.getData();
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Houve um erro ao inserir os dados, tente novamente', 'negative'); 
          })

      },

      showNotif (message: string, color: string) {
        this.$q.notify({
          color,
          message,
          position: 'center',
          multiLine: true,
        });
      },

      async getData() { 
        this.$q.loading.show({});

        await api.get('/cliente/get.php')
          .then((response) => {

            let data: resposeData = response.data as resposeData;

            if(data.status == 'success') {
              this.rows = [];
              this.rows = data.list;
              this.$q.loading.hide();
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Loading failed', 'negative'); 
          })
      },

      async cadastrar() {
        await this.getCidade();        
        this.showDialog = true;
        this.idCliente = 0;
        this.clienteNome = '';
        this.clienteEmail = '';
        this.clienteTelefone = '';
        this.cidadeSelect = {label: '', value: 0};
      },

      async getCidade() { 
        this.$q.loading.show({});

        await api.get('/cidade/get.php')
          .then((response) => {

            let data: resposeDataCidade = response.data as resposeDataCidade;

            if(data.status == 'success') {
              this.listaCidade = [];
              data.list.map(d => {
                this.listaCidade.push({label: d.cidade, value: d.idCidade})
              })
              this.$q.loading.hide();
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Loading failed', 'negative'); 
          })
      },
    }
});
</script>

<style scoped>
.q-page {
    min-height: auto !important;
}
</style>