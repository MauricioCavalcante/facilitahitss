<?php

namespace Modules\Ana\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnaEscoposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $escopos = [
            [
                // ID da Coordenacao: 1 (COGED)
                'escopo' => 'B - Coordenação de Gestão de Dados (COGED): apoio nas atividades de desenvolvimento de Painéis ou Fluxos de Trabalho.
• Apoiar a atualização e divulgação de documento de fase de processo de BI;
• Apoiar a atualização e divulgação de normativo ou melhor prática de fase do processo de BI;
• Apoiar na definição de documento de fase de processo de BI;
• Apoiar na dicionarização de dados;
• Apoiar na identificação e correção de dados;
• Apoiar o desenvolvimento de interface;
• Apoiar o desenvolvimento de relatório;
• Desenhar arquitetura da solução;
• Gerar modelo reverso a partir de base de dados;
• Validar modelo de dados.',
                'coordenacao_id' => 1,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-07-29 00:53:48'
            ],
            [
                // ID da Coordenacao: 2 (COGTI)
                'escopo' => 'A - Coordenação de Governança de Tecnologia da Informação (COGTI):
A. Checklists (verificação de registros/relatórios de entrega);
B. Documentos gerados no Sistema Eletrônico de Informações da ANA (com respectivos Printscreens);
C. Relatório(s) técnico(s);
D. Atas de reuniões e notas técnicas (quando couber);
E. Timesheet da equipe alocada na execução da OS (obrigatório).',
                'coordenacao_id' => 2,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-11-04 22:19:22'
            ],
            [
                // ID da Coordenacao: 3 (COOPI) - Escopo A
                'escopo' => 'A - Coordenação de Operação de Infraestrutura de Tecnologia da Informação (COOPI): apoio à gestão dos contratos e dos processos pertinentes à Coordenação.
• Aplicar checklist em artefatos gerados pela contratada na gestão dos contratos;
• Apoiar a implementação de gráfico em painel de monitoramento;
• Apoiar a confecção de Ordens de Serviço para os meses de execução subsequentes;
• Apoiar a gestão financeira dos contratos;
• Apoiar o desenvolvimento de relatório;
• Disponibilizar dashboard de acompanhamento;
• Apoiar implantação de tecnologia ou ferramenta.',
                'coordenacao_id' => 3,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-07-29 00:49:13'
            ],
            [
                // ID da Coordenacao: 3 (COOPI) - Escopo B
                'escopo' => 'B - Coordenação de Operação de Infraestrutura de Tecnologia da Informação (COOPI): apoio à gestão dos processos de Infraestrutura.
• Aplicar “checklist” em artefatos gerados pela contratada na gestão dos contratos;
• Apoiar a gestão das atividades regimentais da COOPI;
• Avaliação de infraestrutura específica alocada;
• Diagnóstico do Processo;
• Emissão de opinião documentada e fundamentada sobre questão técnica;
• Emitir, sob demanda, de opinião documentada e fundamentada sobre questão técnica;
• Realizar implantação de tecnologia ou ferramenta;
• Revisão do Processo Modelado.',
                'coordenacao_id' => 3,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-07-29 00:49:13'
            ],
            [
                // ID da Coordenacao: 4 (COSIC)
                'escopo' => 'B - Coordenação de Segurança de Informação e Comunicação (COSIC): apoiar a confecção de artefatos de contratação para os processos licitatórios de segurança da informação e comunicações (SIC).
• Apoiar a confecção de artefatos de contratação para os processos licitatórios de segurança da informação e comunicações (SIC);
• Apoiar o desenvolvimento de relatórios, normativos, planos e planejamentos em SIC;
• Apoiar as campanhas de capacitação e conscientização em SIC;
• Disponibilizar dashboard e painéis de acompanhamento e monitoramento de projetos e ações da COSIC;
• Apoiar na gestão dos processos relacionados à segurança da informação e privacidade;
• Apoiar os mapeamentos de processo em segurança da informação;
• Apoiar as atividades relacionadas às competências regimentais.',
                'coordenacao_id' => 4,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-07-29 00:49:34'
            ],
            [
                // ID da Coordenacao: 5 (COSIS) - Escopo A
                'escopo' => 'A - Coordenação de Sistemas e Soluções (COSIS): apoio à gestão de sistemas e soluções (sistemas institucionais e/ou finalísticos).
• Apoiar as atividades de priorização de backlog;
• Aplicar checklist de arquitetura de sistemas;
• Aplicar checklist de código fonte;
• Aplicar checklist de equalização em sistemas que foram equalizados;
• Aplicar checklist de gerência de configuração;
• Aplicar checklist de interface em item de backlog / funcionalidade;
• Aplicar checklist de modelagem de banco de dados;
• Aplicar checklist de projeto e artefatos de visão;
• Aplicar checklist de publicação;
• Aplicar checklist de requisito em item de backlog / funcionalidade;
• Aplicar checklist de scripts de banco de dados;
• Aplicar checklist de segurança de sistemas;
• Aplicar checklist de teste global em sistema;
• Apoiar a atualização e divulgação de “checklist” de mensuração de sistemas;
• Apoiar a atualização e divulgação de “checklist” de qualidade de sistemas;
• Apoiar a atualização e divulgação de “checklist” sobre a gestão de processos de automação;
• Apoio a gestão e sustentação dos sistemas e portais (institucionais e/ou finalísticos);
• Apoio às atividades regimentais da coordenação;
• Apoio no atendimento as demandas de Sustentação de Portais;
• Apoio na validação das demandas de Sustentação de Portais;
• Apoio na gestão de confecção de Procedimentos Operacionais Padrão (POP);
• Apoio na gestão e atualização da wiki dos sistemas e portais.',
                'coordenacao_id' => 5,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-07-29 00:48:36'
            ],
            [
                // ID da Coordenacao: 5 (COSIS) - Escopo B
                'escopo' => 'B - Coordenação de Sistemas e Soluções (COSIS): apoio à gestão dos contratos.
• Apoiar a confecção de Ordens de Serviço para os meses de execução subsequentes;
• Apoiar a gestão financeira dos contratos da COSIS;
• Aplicar “checklist” em artefatos gerados (pré e pós) etapas do processo de desenvolvimento de sistemas com foco no apoio à sua fiscalização;
• Apoiar a implementação de gráfico em painel de monitoramento;
• Apoiar a implementação e gestão de métodos ágeis de desenvolvimento de sistemas;
• Apoiar o desenvolvimento de relatório;
• Disponibilizar dashboard de acompanhamento;
• Apoiar nas reuniões gerenciais e nos assuntos administrativos sobre o contrato;
• Apoiar na gestão e atualização da governança de TI da COSIS;
• Apoiar na confecção de documentos oficiais (ofícios/despachos/nota técnica) pertinentes aos contratos;
• Apoiar na elaboração de documentos de contratação para a nova contratação de Fábrica de Software;
• Apoiar na elaboração de documentos de contratação para a nova contratação de Qualidade de Software.',
                'coordenacao_id' => 5,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-07-29 00:48:36'
            ],
            [
                // ID da Coordenacao: 7 (ASGOV)
                'escopo' => 'D - Assessoria Especial de Governança (ASGOV): apoio nas atividades de manutenção de sistemas e do painel de gestão.
• Apoiar na manutenção do SIGEST - módulo de gestão estratégica;
• Apoiar na manutenção do SIGEST - módulo de gestão de riscos;
• Apoiar na manutenção do SIGEST - módulo de gestão orçamentária;
• Apoiar na manutenção do SIGEST - módulo de agenda regulatória;
• Apoiar na manutenção dos painéis da ASGOV;
• Apoiar na manutenção do painel de acompanhamento da PLOA;
• Apoiar as reuniões de monitoramento da estratégia no SIGEST.',
                'coordenacao_id' => 7,
                'created_at' => '2025-03-05 12:30:05',
                'updated_at' => '2025-11-04 22:13:54'
            ],
            [
                // ID da Coordenacao: 6 (CPLAC) - Escopo B
                'escopo' => 'B - Coordenação de Planejamento e Contratos (CPLAC): apoio à gestão das atividades regimentais da coordenação e dos processos de contratações de Tecnologia da Informação e de Comunicação (TIC).
• Apoiar na gestão dos contratos da CPLAC e no monitoramento dos contratos da STI;
• Apoiar na elaboração de artefatos de planejamento da contratação das contratações priorizadas pela STI;
• Apoiar no desenvolvimento de ações que fomentem a melhoria contínua do processo de monitoramento dos contratos e do processo de contratação de TIC e das demais ações pertinentes a esta coordenação;
• Apoiar nas atividades regimentais da coordenação.',
                'coordenacao_id' => 6,
                'created_at' => '2025-03-23 17:23:20',
                'updated_at' => '2025-07-29 00:50:37'
            ],
            [
                // ID da Coordenacao: 5 (COSIS) - Escopo C (ID 13)
                'escopo' => 'C - Coordenação de Sistemas e Soluções (COSIS): apoio à gestão de métricas e mensuração de sistemas.
• Realizar contagem de pontos de função e demais métricas de sistemas;
• Validar medições e contagens realizadas por terceiros;
• Elaborar e atualizar diretrizes, normativos e checklists de medição de sistemas;
• Apoiar a elaboração de relatórios e dashboards de acompanhamento de métricas;
• Consolidar e disponibilizar indicadores de desempenho dos sistemas institucionais e finalísticos.',
                'coordenacao_id' => 5,
                'created_at' => '2025-11-04 22:13:39',
                'updated_at' => '2025-11-04 22:13:39'
            ],
            [
                // ID da Coordenacao: 6 (CPLAC) - Escopo D (ID 14)
                'escopo' => 'B - Coordenação de Planejamento e Contratos (CPLAC):
A. Checklists (verificação de registros/Relatórios de Entrega, Notas Fiscais);
B. Atualizações na ferramenta de Gestão/PowerBI (com respectivos Printscreens ou relatórios);
C. Relatórios técnicos;
D. Atas de reuniões entre áreas de negócios e CONTRATADA;
E. Notas e diagnósticos técnicos (quando couber);
F. Timesheet da equipe alocada na execução da OS (obrigatório).',
                'coordenacao_id' => 6,
                'created_at' => '2025-11-04 22:19:55',
                'updated_at' => '2025-11-04 22:19:55'
            ],
        ];

        // Insere os dados na tabela, usando o 'escopo' e 'coordenacao_id' para identificar duplicatas
        DB::table('ana_escopos')->upsert($escopos, ['escopo', 'coordenacao_id']);
    }
}
