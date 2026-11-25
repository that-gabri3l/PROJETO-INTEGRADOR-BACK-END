-- Cria 10 vagas de demonstração em rascunho para cada empresa cadastrada.
-- Pode ser executado novamente: os modelos já inseridos não serão duplicados.
USE jobboard_bd;

INSERT INTO vagas (
    empresa_id,
    titulo,
    area,
    regime,
    localizacao,
    salario_min,
    salario_max,
    exibir_salario,
    descricao,
    requisitos,
    beneficios,
    status
)
SELECT
    modelos.empresa_id,
    modelos.titulo,
    modelos.area,
    modelos.regime,
    modelos.localizacao,
    NULL,
    NULL,
    FALSE,
    modelos.descricao,
    modelos.requisitos,
    modelos.beneficios,
    'rascunho'
FROM (
    SELECT
        e.id AS empresa_id,
        CASE e.setor
            WHEN 'Tecnologia' THEN CASE m.numero
                WHEN 1 THEN 'Desenvolvedor(a) de software'
                WHEN 2 THEN 'Analista de dados'
                WHEN 3 THEN 'Analista de qualidade de software'
                WHEN 4 THEN 'Analista de suporte técnico'
                WHEN 5 THEN 'Designer UX/UI'
                WHEN 6 THEN 'Engenheiro(a) de software'
                WHEN 7 THEN 'Analista de segurança da informação'
                WHEN 8 THEN 'Gerente de produto digital'
                WHEN 9 THEN 'Analista de infraestrutura'
                ELSE 'Estagiário(a) de tecnologia'
            END
            WHEN 'Finanças' THEN CASE m.numero
                WHEN 1 THEN 'Analista financeiro'
                WHEN 2 THEN 'Assistente financeiro'
                WHEN 3 THEN 'Analista contábil'
                WHEN 4 THEN 'Analista de crédito'
                WHEN 5 THEN 'Analista de investimentos'
                WHEN 6 THEN 'Controller financeiro'
                WHEN 7 THEN 'Consultor(a) financeiro(a)'
                WHEN 8 THEN 'Analista de cobrança'
                WHEN 9 THEN 'Auditor(a) interno(a)'
                ELSE 'Estagiário(a) financeiro(a)'
            END
            WHEN 'Saúde' THEN CASE m.numero
                WHEN 1 THEN 'Enfermeiro(a)'
                WHEN 2 THEN 'Técnico(a) de enfermagem'
                WHEN 3 THEN 'Recepcionista de clínica'
                WHEN 4 THEN 'Assistente administrativo de saúde'
                WHEN 5 THEN 'Analista de faturamento hospitalar'
                WHEN 6 THEN 'Farmacêutico(a)'
                WHEN 7 THEN 'Técnico(a) de laboratório'
                WHEN 8 THEN 'Assistente de atendimento ao paciente'
                WHEN 9 THEN 'Coordenador(a) de serviços de saúde'
                ELSE 'Estagiário(a) da área da saúde'
            END
            WHEN 'Educação' THEN CASE m.numero
                WHEN 1 THEN 'Professor(a)'
                WHEN 2 THEN 'Coordenador(a) pedagógico(a)'
                WHEN 3 THEN 'Assistente de sala de aula'
                WHEN 4 THEN 'Orientador(a) educacional'
                WHEN 5 THEN 'Designer instrucional'
                WHEN 6 THEN 'Tutor(a) educacional'
                WHEN 7 THEN 'Secretário(a) escolar'
                WHEN 8 THEN 'Assistente de conteúdo didático'
                WHEN 9 THEN 'Analista de educação'
                ELSE 'Estagiário(a) de pedagogia'
            END
            WHEN 'Varejo' THEN CASE m.numero
                WHEN 1 THEN 'Atendente de loja'
                WHEN 2 THEN 'Operador(a) de caixa'
                WHEN 3 THEN 'Vendedor(a)'
                WHEN 4 THEN 'Fiscal de loja'
                WHEN 5 THEN 'Estoquista'
                WHEN 6 THEN 'Gerente de loja'
                WHEN 7 THEN 'Analista de e-commerce'
                WHEN 8 THEN 'Assistente de compras'
                WHEN 9 THEN 'Visual merchandiser'
                ELSE 'Auxiliar de logística de loja'
            END
            WHEN 'Logística' THEN CASE m.numero
                WHEN 1 THEN 'Analista de logística'
                WHEN 2 THEN 'Assistente de logística'
                WHEN 3 THEN 'Conferente de mercadorias'
                WHEN 4 THEN 'Operador(a) de estoque'
                WHEN 5 THEN 'Coordenador(a) de transporte'
                WHEN 6 THEN 'Analista de roteirização'
                WHEN 7 THEN 'Assistente de expedição'
                WHEN 8 THEN 'Analista de cadeia de suprimentos'
                WHEN 9 THEN 'Supervisor(a) de operações logísticas'
                ELSE 'Estagiário(a) de logística'
            END
            ELSE CASE m.numero
                WHEN 1 THEN 'Assistente administrativo'
                WHEN 2 THEN 'Analista de recursos humanos'
                WHEN 3 THEN 'Assistente de atendimento'
                WHEN 4 THEN 'Analista comercial'
                WHEN 5 THEN 'Assistente de marketing'
                WHEN 6 THEN 'Analista de operações'
                WHEN 7 THEN 'Assistente financeiro'
                WHEN 8 THEN 'Analista de comunicação'
                WHEN 9 THEN 'Coordenador(a) de projetos'
                ELSE 'Estagiário(a) administrativo(a)'
            END
        END AS titulo,
        COALESCE(NULLIF(TRIM(e.setor), ''), 'Outro') AS area,
        CASE
            WHEN e.setor IN ('Saúde', 'Varejo', 'Logística') THEN 'Presencial'
            WHEN MOD(m.numero, 3) = 1 THEN 'Híbrido'
            WHEN MOD(m.numero, 3) = 2 THEN 'Remoto'
            ELSE 'Presencial'
        END AS regime,
        NULLIF(TRIM(e.cidade_sede), '') AS localizacao,
        CONCAT(
            'Modelo de demonstração ', e.id, '-', m.numero,
            '. Revise as atividades e as informações desta vaga antes de publicá-la.'
        ) AS descricao,
        'Modelo de demonstração. Defina os requisitos e qualificações para esta vaga antes da publicação.' AS requisitos,
        'Benefícios a definir pela empresa.' AS beneficios
    FROM empresas e
    CROSS JOIN (
        SELECT 1 AS numero UNION ALL
        SELECT 2 UNION ALL
        SELECT 3 UNION ALL
        SELECT 4 UNION ALL
        SELECT 5 UNION ALL
        SELECT 6 UNION ALL
        SELECT 7 UNION ALL
        SELECT 8 UNION ALL
        SELECT 9 UNION ALL
        SELECT 10
    ) AS m
) AS modelos
WHERE NOT EXISTS (
    SELECT 1
    FROM vagas existentes
    WHERE existentes.empresa_id = modelos.empresa_id
      AND existentes.descricao = modelos.descricao
);
